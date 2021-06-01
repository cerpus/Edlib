import appConfig from './app.js';

export default {
    url: process.env.DOKU_PROXY_API_SENTRY_URL,
    enable: appConfig.isProduction,
};
