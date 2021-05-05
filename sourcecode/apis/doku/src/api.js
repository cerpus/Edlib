import { setupApi } from '@cerpus-private/edlib-node-utils/index.js';
import router from './routes/index.js';
import errorReportingConfig from './config/errorReporting.js';

setupApi(router, {
    errorReportingConfig,
});
