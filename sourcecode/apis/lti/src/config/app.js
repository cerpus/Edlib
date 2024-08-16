import { config, env } from '../node-utils/index.js';

export default {
    isProduction: config.app.isProduction,
    features: {
        autoUpdateLtiUsage:
            env('EDLIBCOMMON_FEATURE_AUTO_UPDATE_LTI_USAGE', 'false') ===
            'true',
    },
};
