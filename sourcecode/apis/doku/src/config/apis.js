import { env } from '@cerpus/edlib-node-utils';

export default {
    license: {
        url: env('LICENSEAPI_URL', 'http://licenseapi'),
    },
    version: {
        url: env('VERSIONAPI_URL', 'http://versionapi'),
    },
    coreInternal: {
        url: env('CORE_INTERNAL_URL', 'http://core'),
    },
    id: {
        url: env('ID_URL', 'http://idapi'),
    },
};
