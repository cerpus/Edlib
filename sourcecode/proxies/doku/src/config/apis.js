import { env } from '@cerpus/edlib-node-utils';

export default {
    doku: {
        url: env('APIS_DOKU_API_URL', 'http://dokuapi'),
    },
    core: {
        url: env('CORE_URL', 'https://core-external.local'),
    },
    auth: {
        url: env('AUTHAPI_URL', 'https://auth.local'),
        clientId: env(
            'AUTHAPI_CLIENT_ID',
            'b629aec5-58fb-42df-b58e-753affe8f868'
        ),
        secret: env('AUTHAPI_SECRET', '91bbc285-a57a-405c-97cb-c9549ced42f0'),
    },
};
