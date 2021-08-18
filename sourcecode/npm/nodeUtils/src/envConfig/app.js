import env from '../services/env.js';

export default {
    isProduction: env('NODE_ENV', 'development') === 'production',
    isTest: env('NODE_ENV', 'development') === 'test',
    port: parseInt(env('PORT', 80)),
    enableLogging: env('ENABLE_LOGGING', '1') === '1',
    logRequests: env('LOG_REQUESTS', '1') === '1',
    cookieDomain: env('CONFIG_COOKIE_DOMAIN'),
    environment: env('POD_NAMESPACE', 'local'),
    shouldEnableDevFeatures: env('DEPLOYMENT_ENVIRONMENT', 'dev') === 'dev',
    serviceName: env('SERVICE_NAME'),
    logstashUrl: env('EDLIBCOMMON_LOGSTASH_URL', null),
    displayDetailedErrors:
        env('EDLIBCOMMON_DISPLAY_DETAILED_ERRORS', 'false') === 'true',
};
