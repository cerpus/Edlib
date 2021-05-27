import appConfig from './app.js';

export default {
    url: process.env.DOKU_API_SENTRY_URL,
    enable: appConfig.isProduction,
};
