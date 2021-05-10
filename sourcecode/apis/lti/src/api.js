import { setupApi } from '@cerpus/edlib-node-utils/index.js';
import router from './routes/index.js';
import errorReportingConfig from './config/errorReporting.js';
import { buildRawContext } from './context/index.js';
import fileParserService from './services/fileParser.js';
import consumerService from './services/consumer.js';

const start = async () => {
    const context = buildRawContext();

    // Set consumers from configuration file
    const { consumers } = fileParserService.getConfigurationValuesFromSetupFile(
        'consumers.yaml'
    );

    if (Array.isArray(consumers)) {
        await Promise.all(
            consumers
                .filter((consumer) => consumer.key && consumer.secret)
                .map(({ key, secret }) =>
                    consumerService.createOrUpdate(context, key, secret)
                )
        );
    }

    setupApi(router, {
        errorReportingConfig,
    });
};

start();
