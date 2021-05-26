import { setupApi } from '@cerpus/edlib-node-utils/index.js';
import router from './routes/index.js';
import errorReportingConfig from './config/errorReporting.js';
import { pubsub } from '@cerpus/edlib-node-utils/services/index.js';

const start = async () => {
    const pubSubConnection = await pubsub.setup();

    setupApi(() => router({ pubSubConnection }), {
        errorReportingConfig,
    });
};

start();
