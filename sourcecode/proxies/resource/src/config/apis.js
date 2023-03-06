import { env } from '@cerpus/edlib-node-utils';

export default {
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
