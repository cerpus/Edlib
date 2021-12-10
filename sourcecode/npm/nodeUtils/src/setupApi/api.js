import './setupDotenv.js';
import logger from '../services/logger.js';
import appConfig from '../envConfig/app.js';
import app from './app.js';

const start = async (buildRouter, config) => {
    const compiledApp = await app(buildRouter, config);

    compiledApp.listen(appConfig.port, () => {
        logger.info(`API listening on ${appConfig.port}`);
    });
};

export default (buildRouter, config) => {
    start(buildRouter, config).catch((error) => {
        logger.error(`Couldn't compile router`);
        logger.error(error.stack);
        process.exit(1);
    });
};
