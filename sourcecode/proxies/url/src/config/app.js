import { config } from '@cerpus/edlib-node-utils';

export default {
    shouldEnableDevFeatures: config.app.shouldEnableDevFeatures,
    isProduction: config.app.isProduction,
    coreUrl: process.env.CORE_URL,
};
