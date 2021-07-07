import { setupApi, pubsub } from '@cerpus/edlib-node-utils';
import router from './routes/index.js';
import errorReportingConfig from './config/errorReporting.js';

const start = async () => {
    const pubSubConnection = await pubsub.setup();

    setupApi(() => router({ pubSubConnection }), {
        errorReportingConfig,
        trustProxy: true,
        extraViewDir: 'src/views',
    });
};

start();
