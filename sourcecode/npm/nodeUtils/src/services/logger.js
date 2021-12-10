import Winston from 'winston';
import appConfig from '../envConfig/app.js';

const logger = Winston.createLogger({
    level: 'debug',
    format: Winston.format.json(),
    silent: !appConfig.enableLogging,
    transports: [new Winston.transports.Console()],
});

export default logger;
