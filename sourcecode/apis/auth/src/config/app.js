import { config, env } from '@cerpus/edlib-node-utils';

export default {
    isProduction: config.app.isProduction,
    allowFakeToken: env('EDLIBCOMMON_LOCAL_DEVELOPMENT', 'false') === 'true',
};
