import { config } from '@cerpus/edlib-node-utils/index.js';
import env from '@cerpus/edlib-node-utils/services/env.js';

export default {
    isProduction: config.app.isProduction,
    allowFakeToken: env('EDLIBCOMMON_LOCAL_DEVELOPMENT', 'false') === 'true',
};
