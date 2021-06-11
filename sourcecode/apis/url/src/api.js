import router from './routes/index.js';
import errorReportingConfig from './config/errorReporting.js';
import { pubsub, setupApi } from '@cerpus/edlib-node-utils';
import sync from './subscribers/sync.js';

const start = async () => {
    const pubSubConnection = await pubsub.setup();

    await Promise.all(
        [
            {
                exchangeName: '__internal_edlibUrl_sync',
                subscriptionName: '__internal_edlibUrl_sync-urlapi_handler',
                handler: sync,
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

    setupApi(() => router({ pubSubConnection }), {
        errorReportingConfig,
    });
};

start();
