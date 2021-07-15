import { setupApi } from '@cerpus/edlib-node-utils';
import router from './routes/index.js';
import errorReportingConfig from './config/errorReporting.js';

setupApi(router, {
    errorReportingConfig,
    trustProxy: true,
    extraViewDir: 'src/views',
});
