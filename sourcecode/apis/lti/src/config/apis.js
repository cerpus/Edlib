import env from '@cerpus/edlib-node-utils/services/env.js';

export default {
    coreInternal: {
        url: env('CORE_INTERNAL_URL', 'http://core'),
    },
};
