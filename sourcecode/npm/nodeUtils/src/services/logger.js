import Winston from 'winston';
import appConfig from '../envConfig/app.js';
import LogstashTransport from './logstash.js';

const logger = Winston.createLogger({
    level: 'debug',
    format: Winston.format.combine(
        Winston.format.errors({ stack: true }),
        Winston.format.simple()
    ),
    silent: !appConfig.enableLogging,
    transports: [
        new Winston.transports.Console(),
        !appConfig.isTest &&
            new LogstashTransport({
                logstash: {
                    host: 'logstash.elk',
                    port: 9605,
                },
            }),
    ].filter(Boolean),
});

export default logger;
