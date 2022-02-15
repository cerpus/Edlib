import { env } from '@cerpus/edlib-node-utils';

export default {
    licenseApi: {
        url: env('LICENSEAPI_URL', 'http://licenseapi'),
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
