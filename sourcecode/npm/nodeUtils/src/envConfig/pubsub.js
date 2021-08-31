import env from '../services/env.js';

export default {
    url: env('EDLIBCOMMON_RABBITMQ_URL', 'amqp://rabbitmq'),
    user: env('EDLIBCOMMON_RABBITMQ_USER', 'guest'),
    password: env('EDLIBCOMMON_RABBITMQ_PASSWORD', 'guest'),
};
