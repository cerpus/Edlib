import appConfig from './app.js';

export default {
    url: process.env.LTI_API_SENTRY_URL,
    enable: appConfig.isProduction,
};
