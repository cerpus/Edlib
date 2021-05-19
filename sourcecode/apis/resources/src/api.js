import { setupApi } from '@cerpus/edlib-node-utils/index.js';
import router from './routes/index.js';
import errorReportingConfig from './config/errorReporting.js';
import { setup as setupPubSub, subscribe } from './services/pubSub.js';
import saveEdlibResourcesAPI from './subscribers/saveEdlibResourcesAPI.js';
import sync from './subscribers/sync.js';

const start = async () => {
    const pubSubConnection = await setupPubSub();

    await Promise.all(
        [
            {
                exchangeName: 'edlibResourceUpdate',
                subscriptionName: 'saveToEdlibResourcesAPI',
                handler: saveEdlibResourcesAPI,
            },
            {
                exchangeName: '__internal_edlibResource_sync',
                subscriptionName: 'sync',
                handler: sync,
            },
        ].map((subscriber) => {
            const handler = subscriber.handler({ pubSubConnection });

            return subscribe(
                pubSubConnection,
                subscriber.exchangeName,
                subscriber.subscriptionName,
                async (msg) => {
                    await handler(JSON.parse(msg.content));
                }
            );
        })
    );

    setupApi(() => router({ pubSubConnection }), {
        errorReportingConfig,
    });
};

start();
