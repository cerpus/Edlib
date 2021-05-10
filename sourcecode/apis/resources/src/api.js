import { setupApi } from '@cerpus/edlib-node-utils/index.js';
import router from './routes/index.js';
import errorReportingConfig from './config/errorReporting.js';
import { setup as setupPubSub, subscribe } from './services/pubSub.js';
import saveEdlibResourcesAPI from './subscribers/saveEdlibResourcesAPI.js';
import sync from './subscribers/sync.js';

const start = async () => {
    const pubSubConnection = await setupPubSub();

    await subscribe(
        pubSubConnection,
        'edlibResourceUpdate',
        'saveToEdlibResourcesAPI',
        async (msg) => {
            await saveEdlibResourcesAPI(JSON.parse(msg.content));
        }
    );
    await subscribe(
        pubSubConnection,
        '__internal_edlibResource_sync',
        'sync',
        async () => {
            await sync();
        }
    );

    setupApi(router, {
        errorReportingConfig,
    });
};

start();
