import env from '../../services/env.js';

describe('Env config', () => {
    describe('db', () => {
        const OLD_ENV = process.env;

        beforeEach(() => {
            jest.resetModules();
            process.env = { ...OLD_ENV };
        });

        afterAll(() => {
            process.env = OLD_ENV;
        });

        const tests = [
            {
                input: {
                    DB_DATABASE: 'asdf',
                },
                output: {
                    host: 'mysql',
                    user: 'root',
                    password: 'mysqlpassword',
                    database: 'asdf',
                    port: 3306,
                },
            },
            {
                input: {
                    DB_HOST: 'hgost',
                    DB_USER: 'user',
                    DB_PASSWORD: 'password',
                    DB_DATABASE: 'asdf',
                    DB_PORT: '12',
                },
                output: {
                    host: 'hgost',
                    user: 'user',
                    password: 'password',
                    database: 'asdf',
                    port: 12,
                },
            },
            {
                input: {
                    DB_HOST: 'hgost',
                    DB_USER: 'user',
                    DB_PASSWORD: 'password',
                    DB_DATABASE: 'asdf',
                    DB_PORT: '12',
                    EDLIBCOMMON_DB_HOST: 'ed-hgost',
                    EDLIBCOMMON_DB_USER: 'eduser',
                    EDLIBCOMMON_DB_PASSWORD: 'edpassword',
                    EDLIBCOMMON_DB_DATABASE: 'edasdf',
                    EDLIBCOMMON_DB_PORT: '13',
                },
                output: {
                    host: 'hgost',
                    user: 'user',
                    password: 'password',
                    database: 'asdf',
                    port: 12,
                },
            },
            {
                input: {
                    EDLIBCOMMON_DB_HOST: 'ed-hgost',
                    EDLIBCOMMON_DB_USER: 'eduser',
                    EDLIBCOMMON_DB_PASSWORD: 'edpassword',
                    EDLIBCOMMON_DB_PORT: '13',
                },
                output: {
                    host: 'ed-hgost',
                    user: 'eduser',
                    password: 'edpassword',
                    database: 'mydb',
                    port: 13,
                },
            },
            {
                input: {
                    EDLIBCOMMON_DB_PREFIX: 'prefix',
                    DB_NAME: 'dbname',
                },
                output: {
                    host: 'mysql',
                    user: 'root',
                    password: 'mysqlpassword',
                    database: 'prefixdbname',
                    port: 3306,
                },
            },
        ];

        for (let testData of tests) {
            test(
                'returns correct config: ' + JSON.stringify(testData.input),
                () => {
                    Object.entries(testData.input).forEach(
                        ([key, value]) => (process.env[key] = value)
                    );

                    const config = require('../db.js').default;
                    expect(config).toStrictEqual(testData.output);
                }
            );
        }
    });
});
