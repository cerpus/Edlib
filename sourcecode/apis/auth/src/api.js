import router from './routes/index.js';
import errorReportingConfig from './config/errorReporting.js';
import { pubsub, setupApi } from '@cerpus/edlib-node-utils';
import gdprDeleteRequest from './subscribers/gdprDeleteRequest.js';

const start = async () => {
    const pubSubConnection = await pubsub.setup();

    await Promise.all(
        [
            {
                exchangeName: 'edlib_gdpr_delete_request',
                handler: gdprDeleteRequest,
            },
        ].map((subscriber) => {
            const handler = subscriber.handler({ pubSubConnection });

            return pubsub.subscribe(
                pubSubConnection,
                subscriber.exchangeName,
                subscriber.exchangeName + '-authapi',
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
