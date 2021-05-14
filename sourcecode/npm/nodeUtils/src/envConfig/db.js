import env from '../services/env.js';

const getDatabase = () => {
    if (process.env.DB_DATABASE) {
        return process.env.DB_DATABASE;
    }

    if (process.env.EDLIBCOMMON_DB_PREFIX && process.env.DB_NAME) {
        return `${process.env.EDLIBCOMMON_DB_PREFIX}${process.env.DB_NAME}`;
    }

    return 'mydb';
};

export default {
    host: env('DB_HOST', env('EDLIBCOMMON_DB_HOST', 'mysql')),
    user: env('DB_USER', env('EDLIBCOMMON_DB_USER', 'root')),
    password: env(
        'DB_PASSWORD',
        env('EDLIBCOMMON_DB_PASSWORD', 'mysqlpassword')
    ),
    database: getDatabase(),
    port: parseInt(env('DB_PORT', env('EDLIBCOMMON_DB_PORT', 3306))),
};
