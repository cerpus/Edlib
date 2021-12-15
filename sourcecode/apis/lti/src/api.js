import { setupApi } from '@cerpus/edlib-node-utils';
import router from './routes/index.js';
import errorReportingConfig from './config/errorReporting.js';
import { pubsub } from '@cerpus/edlib-node-utils';

const start = async () => {
    const pubSubConnection = await pubsub.setup();

    setupApi(async () => router({ pubSubConnection }), {
        errorReportingConfig,
    });
};

start();
