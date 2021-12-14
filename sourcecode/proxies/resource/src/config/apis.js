import { env } from '@cerpus/edlib-node-utils';

export default {
    licenseApi: {
        url: env('LICENSEAPI_URL', 'http://licenseapi'),
    },
    auth: {
        url: env('AUTHAPI_URL', 'https://auth.local'),
        clientId: env(
            'AUTHAPI_CLIENT_ID',
            '91baeff3-e570-4b57-9ef2-d595fab69dfc'
        ),
        secret: env('AUTHAPI_SECRET', '89f2e7f0-549e-47d0-85c8-016e5b5f8587'),
    },
    core: {
        url: env('CORE_URL', 'https://core-external.local'),
    },
    coreInternal: {
        url: env('CORE_INTERNAL_URL', 'http://core'),
    },
    id: {
        url: env('ID_URL', 'http://idapi'),
    },
    version: {
        url: env('VERSIONAPI_URL', 'http://versionapi'),
    },
    resource: {
        url: env('RESOURCE_API_URL', 'http://resourceapi'),
    },
    edlibAuth: {
        url: env('EDLIB_AUTH_URL', 'http://authapi'),
    },
};
