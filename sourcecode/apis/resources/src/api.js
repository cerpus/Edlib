import { setupApi } from '@cerpus/edlib-node-utils/index.js';
import router from './routes/index.js';
import errorReportingConfig from './config/errorReporting.js';
import saveEdlibResourcesAPI from './subscribers/saveEdlibResourcesAPI.js';
import sync from './subscribers/sync.js';
import newUser from './subscribers/newUser.js';
import { pubsub } from '@cerpus/edlib-node-utils/services/index.js';
import { buildRawContext } from './context/index.js';

const start = async () => {
    const pubSubConnection = await pubsub.setup();

    await Promise.all(
        [
            {
                exchangeName: 'edlibResourceUpdate',
                subscriptionName: 'edlibResourceUpdate-resourceapi_handler',
                handler: saveEdlibResourcesAPI,
            },
            {
                exchangeName: '__internal_edlibResource_sync',
                subscriptionName:
                    '__internal_edlibResource_sync-resourceapi_handler',
                handler: sync,
            },
            {
                exchangeName: 'edlib_new_user',
                subscriptionName: 'edlib_new_user-resourceapi_handler',
                handler: newUser,
            },
        ].map((subscriber) => {
            const handler = subscriber.handler({ pubSubConnection });

            return pubsub.subscribe(
                pubSubConnection,
                subscriber.exchangeName,
                subscriber.subscriptionName,
                async (msg) => {
                    await handler(JSON.parse(msg.content));
                }
            );
        })
    );

    const context = await buildRawContext({}, {}, { pubSubConnection });
    await context.services.elasticsearch.createOrIgnoreIndex();

    setupApi(() => router({ pubSubConnection }), {
        errorReportingConfig,
    });
};

start();
