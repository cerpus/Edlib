import { env } from '@cerpus/edlib-node-utils';

export default {
    core: {
        url: env('CORE_URL', 'https://core-external.local'),
        key: env('CORE_KEY', 'd37b451d-cd9d-42b2-84d5-f2310dca633a'),
        secret: env('CORE_SECRET', 'f91993b8-51a7-4fd7-b4dc-da5346a5a4d2'),
    },
    auth: {
        url: env('AUTHAPI_URL', 'https://auth.local'),
        clientId: env(
            'AUTHAPI_CLIENT_ID',
            'b629aec5-58fb-42df-b58e-753affe8f868'
        ),
        secret: env('AUTHAPI_SECRET', '91bbc285-a57a-405c-97cb-c9549ced42f0'),
    },
    coreInternal: {
        url: env('CORE_INTERNAL_URL', 'http://core'),
    },
    resource: {
        url: env('RESOURCE_API_URL', 'http://resourceapi'),
    },
    lti: {
        url: env('LTI_API_URL', 'http://ltiapi'),
    },
    edlibAuth: {
        url: env('EDLIB_AUTH_URL', 'http://authapi'),
    },
};
