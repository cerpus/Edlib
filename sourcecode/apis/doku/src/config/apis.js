import env from '@cerpus/edlib-node-utils/services/env.js';

export default {
    license: {
        url: env('LICENSEAPI_URL', 'http://licenseapi:8081'),
    },
    version: {
        url: env('VERSIONAPI_URL', 'http://versioningapi:8080'),
    },
    coreInternal: {
        url: env('CORE_INTERNAL_URL', 'http://core'),
    },
    id: {
        url: env('ID_URL', 'http://idapi'),
    },
};
