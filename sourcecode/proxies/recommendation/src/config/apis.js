import { env } from '@cerpus/edlib-node-utils';

export default {
    recommender: {
        url: env('APIS_RECOMMENDER_URL', 'http://re-recommender'),
    },
    auth: {
        url: env('AUTHAPI_URL', 'https://auth.local'),
        clientId: env(
            'AUTHAPI_CLIENT_ID',
            'b629aec5-58fb-42df-b58e-753affe8f868'
        ),
        secret: env('AUTHAPI_SECRET', '91bbc285-a57a-405c-97cb-c9549ced42f0'),
    },
    core: {
        url: env('CORE_URL', 'https://core-external.local'),
    },
    coreInternal: {
        url: env('CORE_INTERNAL_URL', 'http://core'),
    },
    edlibAuth: {
        url: env('EDLIB_AUTH_URL', 'http://authapi'),
    },
    resource: {
        url: env('RESOURCE_API_URL', 'http://resourceapi'),
    },
};
