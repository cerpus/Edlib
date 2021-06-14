import { config, env } from '@cerpus/edlib-node-utils';

export default {
    isProduction: config.app.isProduction,
    features: {
        autoUpdateLtiUsage:
            env('EDLIBCOMMON_FEATURE_AUTO_UPDATE_LTI_USAGE', 'false') ===
            'true',
    },
};
