import env from '../services/env.js';

export default {
    url: env('REDIS_URL', env('EDLIBCOMMON_REDIS_URL', 'redis://redis')),
};
