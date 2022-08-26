<?php

namespace App\Http\Controllers;

use App\ApiModels\LtiUser;
use App\Apis\AuthApiService;
use App\Apis\ResourceApiService;
use App\Exceptions\LtiException;
use App\Exceptions\NotFoundException;
use App\Models\LtiRegistration;
use App\Services\Lti\LtiMessageLaunch;
use App\Services\Lti\LtiOIDCLogin;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Packback\Lti1p3\JwksEndpoint;
use Packback\Lti1p3\LtiDeepLinkResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
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
    public function oidcLogin(Request $request, LtiRegistration $registration): Response
    {
        try {
            return LtiOIDCLogin::doLogin($registration, route('lti.launch'), $request->all());
        } catch (LtiException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }
    }

    /**
     * @throws Throwable
     */
    public function getJwksKeys(LtiRegistration $registration): JsonResponse
    {
        $keys = $registration->ltiKeySet->ltiKeys;
        $jwksKeys = [];

        foreach ($keys as $key) {
            $jwksKeys["$key->id"] = $key->private_key;
        }

        return new JsonResponse((new JwksEndpoint($jwksKeys))->getPublicJwks());
    }

    /**
     * @throws Throwable
     */
    public function launch(Request $request): View|\Illuminate\Contracts\View\Factory|JsonResponse|\Illuminate\View\View|\Illuminate\Contracts\Foundation\Application|\Laravel\Lumen\Application
    {
        try {
            $launch = LtiMessageLaunch::fromRequest($request);
        } catch (Exception $e) {
            return response()->json([
                'errors' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $deploymentId = $launch->getLaunchData()['https://purl.imsglobal.org/spec/lti/claim/deployment_id'];
        $externalId = $launch->getLaunchData()['sub'];

        $response = $this->authApiService->createTokenForLtiUser(new LtiUser(
            $launch->registration->id,
            $deploymentId,
            $externalId,
            $launch->getLaunchData()["email"] ?? null,
            $launch->getLaunchData()["given_name"] ?? null,
            $launch->getLaunchData()["family_name"] ?? null,
        ));

        $token = $response['token'];
        $userId = $response['userId'];

        if ($launch->isDeepLinkLaunch()) {
            return view('lti.deepLinkingLaunch', [
                'iframeUrl' => 'https://www.edlib.local/s/lti/browser?jwt=' . $token,
                'returnUrl' => route('lti.deepLinkingReturn', [
                    'launchId' => $launch->getLaunchId()
                ])
            ]);
        }

        if ($launch->isResourceLaunch()) {
            $launchUrlData = parse_url($launch->getLaunchData()['https://purl.imsglobal.org/spec/lti/claim/target_link_uri']);

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

            $jwt = JWT::encode($jwtData, config('internal.toolKey'), 'HS256');

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
        $launch = LtiMessageLaunch::fromCache($request->get('launchId'));

        $resourcesRaw = json_decode($request->get('resources'), true);

        $resources = [];

        foreach ($resourcesRaw as $resource) {
            $resources[] = (new LtiDeepLinkResource())
                ->setType($resource['type'])
                ->setUrl($resource['url'])
                ->setTitle($resource['title'] ?? null);
        }

        return view('lti.deepLinkingReturn', [
            'returnUrl' => $launch->getLaunchData()['https://purl.imsglobal.org/spec/lti-dl/claim/deep_linking_settings']['deep_link_return_url'],
            'jwt' => $launch->getDeepLink()->getResponseJwt($resources)
        ]);
    }
}
