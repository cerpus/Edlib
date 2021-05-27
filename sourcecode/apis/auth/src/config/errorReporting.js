import appConfig from './app.js';

export default {
    url: process.env.AUTH_API_SENTRY_URL,
    enable: appConfig.isProduction,
};
