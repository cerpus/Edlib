import { env } from '@cerpus/edlib-node-utils';

export default {
    coreInternal: {
        url: env('EDLIBCOMMON_CORE_INTERNAL_URL', 'http://core'),
    },
    resource: {
        url: env('RESOURCE_API_URL', 'http://resourceapi'),
    },
};
