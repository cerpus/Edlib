<?php

namespace Tests\Feature\Services\Lti;

use App\Models\LtiRegistration;
use Closure;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\ArrayShape;
use Packback\Lti1p3\JwksEndpoint;
use Tests\TestCase;

class LtiMessageLaunchTest extends TestCase
{
    use RefreshDatabase;

    #[ArrayShape(['cookies' => "mixed|string[]", 'jsonData' => "array", 'cache' => "mixed|string[]", 'setup' => "\Closure"])] private function buildLaunchRequest(array $overrides = []): array
    {
        openssl_pkey_export(openssl_pkey_new(), $privateKey);
        $jwks = new JwksEndpoint([
            '1' => $privateKey
        ]);

        $idToken = [
            'test' => true,
            'aud' => 'client-id',
            'iss' => 'issuer',
            'nonce' => 'asdf',
            'sub' => 'external-user-id',
            'https://purl.imsglobal.org/spec/lti/claim/deployment_id' => '1',
            'https://purl.imsglobal.org/spec/lti/claim/message_type' => 'LtiResourceLinkRequest',
            'https://purl.imsglobal.org/spec/lti/claim/version' => '1.3.0',
            'https://purl.imsglobal.org/spec/lti/claim/roles' => [],
            'https://purl.imsglobal.org/spec/lti/claim/resource_link' => [
                'id' => 'id'
            ],
            'https://purl.imsglobal.org/spec/lti/claim/target_link_uri' => "https://spec.edlib.com/resource-reference?resourceId=1",
        ];

        if (array_key_exists('idToken', $overrides)) {
            $idToken = array_merge($idToken, $overrides['idToken']);
        }

        $jsonData = [
            'id_token' => JWT::encode($idToken, $privateKey, 'RS256', '1')
        ];

        if (key_exists('jsonData', $overrides)) {
            $jsonData = array_merge($jsonData, $overrides['jsonData']);
        } else {
            $jsonData['state'] = 'value1';
        }

        $cookies = [
            'lti1p3_value1' => 'value1'
        ];

        if (key_exists('cookies', $overrides)) {
            $cookies = $overrides['cookies'];
        }

        $cache = [
            'nonce_asdf' => 'asdf'
        ];

        if (key_exists('cache', $overrides)) {
            $cache = $overrides['cache'];
        }

        return [
            'cookies' => $cookies,
            'jsonData' => $jsonData,
            'cache' => $cache,
            'setup' => function () use ($jwks) {
                $ltiDeployment = \App\Models\LtiDeployment::factory([
                    'deployment_id' => '1',
                ])->for(LtiRegistration::factory([
                    'issuer' => 'issuer',
                    'client_id' => 'client-id'
                ]))->create();

                Http::fake([
                    $ltiDeployment->ltiRegistration->platform_key_set_endpoint => Http::response($jwks->getPublicJwks())
                ]);

                Http::fake([
                    "http://authapi/v1/lti-users/token" => Http::response([
                        'token' => 'test',
                        'user' => [
                            'id' => '1'
                        ]
                    ]),
                    "http://resourceapi/v1/tenants/1/resources/1/launch-info" => Http::response([
                        'url' => 'http://test.no',
                        'params' => []
                    ])
                ]);
            }
        ];
    }

    public function invalidRequestDataProvider(): array
    {
        $noIdTokenRequest = $this->buildLaunchRequest();
        unset($noIdTokenRequest['jsonData']['id_token']);

        $invalidIdToken = $this->buildLaunchRequest();
        $invalidIdToken['jsonData']['id_token'] = 'invalid';

        return [
            'Everything empty' => [
                'cookies' => [],
                'jsonData' => []
            ],
            'Missing state cookie' => $this->buildLaunchRequest([
                'cookies' => []
            ]),
            'Wrong state cookie value' => $this->buildLaunchRequest([
                'cookies' => [
                    'lti1p3_value1' => 'value2'
                ]
            ]),
            'No state in body' => $this->buildLaunchRequest([
                'jsonData' => []
            ]),
            'No id_token' => $noIdTokenRequest,
            'Invalid id_token' => $invalidIdToken,
            'Missing nonce in cache' => $this->buildLaunchRequest([
                'cache' => []
            ]),
            'Wrong nonce' => $this->buildLaunchRequest([
                'cache' => [
                    'nonce_asdf2' => 'asdf2'
                ]
            ]),
            'Registration not found' => $this->buildLaunchRequest([
                'idToken' => [
                    'iss' => 'random'
                ]
            ]),
            'Registration not found 2' => $this->buildLaunchRequest([
                'idToken' => [
                    'aud' => 'random'
                ]
            ]),
            'Deployment not found' => $this->buildLaunchRequest([
                'idToken' => [
                    'https://purl.imsglobal.org/spec/lti/claim/deployment_id' => 'random',
                ]
            ]),
            'Unknown message type' => $this->buildLaunchRequest([
                'idToken' => [
                    'https://purl.imsglobal.org/spec/lti/claim/message_type' => 'random',
                ]
            ]),
            'Missing sub' => $this->buildLaunchRequest([
                'idToken' => [
                    'sub' => null,
                ]
            ])
        ];
    }

    /**
     * @dataProvider invalidRequestDataProvider
     */
    public function testInvalidRequests(array $cookies, array $jsonData, array $cache = [], Closure $setup = null): void
    {
        if (!empty($setup)) {
            $setup();
        }

        Cache::setMultiple($cache);

        $this->withCookies($cookies)
            ->withCredentials()
            ->disableCookieEncryption()
            ->postJson("/lti-13/launch", $jsonData)
            ->assertStatus(422);
    }

    public function testValidLaunchRequests(): void
    {
        $data = $this->buildLaunchRequest();
        $data['setup']();

        Cache::setMultiple($data['cache']);

        $this->withCookies($data['cookies'])
            ->withCredentials()
            ->disableCookieEncryption()
            ->postJson("/lti-13/launch", $data['jsonData'])
            ->assertStatus(200);
    }
}
