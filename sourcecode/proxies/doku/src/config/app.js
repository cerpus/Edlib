import { config } from '@cerpus-private/edlib-node-utils/index.js';

export default {
    shouldEnableDevFeatures: config.app.shouldEnableDevFeatures,
    isProduction: config.app.isProduction,
    coreUrl: process.env.CORE_URL,
};
