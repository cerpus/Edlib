import router from './routes/index.js';
import errorReportingConfig from './config/errorReporting.js';
import { pubsub, setupApi } from '@cerpus/edlib-node-utils';

const start = async () => {
    const pubSubConnection = await pubsub.setup();

    setupApi(() => router({ pubSubConnection }), {
        errorReportingConfig,
    });
};

start();
