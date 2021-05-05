import env from '../services/env.js';

export default {
    host: env('DB_HOST', 'mysql'),
    user: env('DB_USER', 'root'),
    password: env('DB_PASSWORD', 'mysqlpassword'),
    database: env('DB_DATABASE', 'mydb'),
    port: parseInt(env('DB_PORT', 3306)),
};
