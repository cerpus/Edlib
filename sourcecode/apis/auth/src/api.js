import router from './routes/index.js';
import errorReportingConfig from './config/errorReporting.js';
import { pubsub, setupApi } from '@cerpus/edlib-node-utils';
import gdprDeleteRequest from './subscribers/gdprDeleteRequest.js';
import authMigrationGetFeedback from './subscribers/authMigrationGetFeedback.js';
import authMigrationExecute from './subscribers/authMigrationExecute.js';

const start = async () => {
    const pubSubConnection = await pubsub.setup();

    await Promise.all(
        [
            {
                exchangeName: 'edlib_gdpr_delete_request',
                handler: gdprDeleteRequest,
            },
            {
                exchangeName: 'auth_migration_get_info',
                handler: authMigrationGetFeedback,
            },
            {
                exchangeName: 'auth_migration_execute',
                handler: authMigrationExecute,
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
