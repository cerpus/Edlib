import { env } from '../node-utils/index.js';

export default {
    coreInternal: {
        url: env('EDLIBCOMMON_CORE_INTERNAL_URL', 'http://core'),
    },
    resource: {
        url: env('RESOURCE_API_URL', 'http://resourceapi'),
    },
};
