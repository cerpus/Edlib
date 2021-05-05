import env from '../services/env.js';

export default {
    url: env('REDIS_URL', 'redis://redis'),
};
