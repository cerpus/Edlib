<?php

namespace App\Http\Controllers;

use App\ApiModels\LtiUser;
use App\Apis\AuthApiService;
use App\Apis\ResourceApiService;
use App\Exceptions\NotFoundException;
use App\Services\Lti\LtiDatabase;
use App\Services\Lti\LtiDeepLink;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use IMSGlobal\LTI\JWKS_Endpoint;
use IMSGlobal\LTI\LTI_Deep_Link_Resource;
use IMSGlobal\LTI\LTI_Message_Launch;
use IMSGlobal\LTI\LTI_OIDC_Login;
use IMSGlobal\LTI\OIDC_Exception;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class Lti13Controller extends Controller
{
    public function __construct(
        private AuthApiService     $authApiService,
        private ResourceApiService $resourceApiService,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function oidcLogin(Request $request): Response
    {
        $login = LTI_OIDC_Login::new(new LtiDatabase());

        try {
            $redirect = $login->do_oidc_login_redirect(route('lti.launch'), $request->all());
        } catch (OIDC_Exception $e) {
            return response()->json([
                'errors' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return redirect($redirect->get_redirect_url());
    }

    /**
     * @throws Throwable
     */
    public function getJwksKeys(Request $request): JsonResponse
    {
        return new JsonResponse(JWKS_Endpoint::from_issuer(new LtiDatabase(), 'http://example.com')->get_public_jwks());
    }

    /**
     * @throws Throwable
     */
    public function launch(Request $request): \Illuminate\Contracts\View\Factory|View|JsonResponse|\Illuminate\View\View|\Illuminate\Contracts\Foundation\Application|\Laravel\Lumen\Application
    {
        $launch = LTI_Message_Launch::new(new LtiDatabase());

        try {
            $launch->validate($request->all());
        } catch (Exception $e) {
            return response()->json([
                'errors' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $clientId = $launch->get_launch_data()['aud'];
        $deploymentId = $launch->get_launch_data()['https://purl.imsglobal.org/spec/lti/claim/deployment_id'];
        $externalId = $launch->get_launch_data()['sub'];

        $response = $this->authApiService->createTokenForLtiUser(new LtiUser(
            $clientId,
            $deploymentId,
            $externalId,
            $launch->get_launch_data()["email"] ?? null,
            $launch->get_launch_data()["given_name"] ?? null,
            $launch->get_launch_data()["family_name"] ?? null,
        ));

        $token = $response['token'];
        $userId = $response['userId'];

        if ($launch->is_deep_link_launch()) {
            return view('lti.deepLinkingLaunch', [
                'iframeUrl' => 'https://www.edlib.local/s/lti/browser?jwt=' . $token,
                'returnUrl' => route('lti.deepLinkingReturn', [
                    'launchId' => $launch->get_launch_id()
                ])
            ]);
        }

        if ($launch->is_resource_launch()) {
            $launchUrlData = parse_url($launch->get_launch_data()['https://purl.imsglobal.org/spec/lti/claim/target_link_uri']);

            if ($launchUrlData['host'] !== 'spec.edlib.com' || $launchUrlData['path'] !== '/resource-reference') {
                return response()->json([
                    'errors' => 'Not a valid reference to resource',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            parse_str($launchUrlData['query'], $queryParameters);

            if (!array_key_exists('resourceId', $queryParameters)) {
                return response()->json([
                    'errors' => 'Not a valid reference to resource',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);

            }

            $resourceId = $queryParameters['resourceId'];
            $resourceVersionId = $queryParameters['resourceVersionId'] ?? null;
            $launchInfo = $this->resourceApiService->getResourceLaunchInfoForTenant($userId, $resourceId, $resourceVersionId);
            $jwtData = array_merge(
                $launchInfo->params,
                [
                    'userToken' => $token
                ]
            );

            $jwt = JWT::encode($jwtData, config('internal.toolKey'));

            return view('lti.viewResourceLaunch', [
                'iframeUrl' => $launchInfo->url . '?jwt=' . $jwt
            ]);
        }

        throw new NotFoundException("message_type");
    }

    /**
     * @throws Throwable
     */
    public function deepLinkingReturn(Request $request)
    {
        $launch = LTI_Message_Launch::from_cache($request->get('launchId'), new LtiDatabase());

        $resourcesRaw = json_decode($request->get('resources'), true);

        $resources = [];

        foreach ($resourcesRaw as $resource) {
            $resources[] = LTI_Deep_Link_Resource::new()
                ->set_type($resource['type'])
                ->set_url($resource['url'])
                ->set_title($resource['title'] ?? null);
        }

        $deepLink = LtiDeepLink::fromLtiLaunch($launch);

        return view('lti.deepLinkingReturn', [
            'returnUrl' => $launch->get_launch_data()['https://purl.imsglobal.org/spec/lti-dl/claim/deep_linking_settings']['deep_link_return_url'],
            'jwt' => $deepLink->get_response_jwt($resources)
        ]);
    }
}
