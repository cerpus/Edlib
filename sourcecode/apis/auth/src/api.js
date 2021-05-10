import { setupApi } from '@cerpus/edlib-node-utils/index.js';
import router from './routes/index.js';
import errorReportingConfig from './config/errorReporting.js';

const start = async () => {
    setupApi(router, {
        errorReportingConfig,
    });
};

start();
