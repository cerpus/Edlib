import { setupApi, pubsub } from '@cerpus/edlib-node-utils';
import router from './routes/index.js';
import errorReportingConfig from './config/errorReporting.js';
import saveEdlibResourcesAPI from './subscribers/saveEdlibResourcesAPI.js';
import migrateOldData from './subscribers/migrateOldData.js';
import refreshElasticsearchIndex from './subscribers/refreshElasticsearchIndex.js';
import newUser from './subscribers/newUser.js';
import { buildRawContext } from './context/index.js';
import jobNames from './constants/jobNames.js';

const start = async () => {
    const pubSubConnection = await pubsub.setup();

    await Promise.all(
        [
            {
                exchangeName: 'edlibResourceUpdate',
                handler: saveEdlibResourcesAPI,
            },
            {
                exchangeName:
                    '__internal_edlibResource_jobs_' +
                    jobNames.MIGRATE_OLD_DATA,
                handler: migrateOldData,
            },
            {
                exchangeName:
                    '__internal_edlibResource_jobs_' +
                    jobNames.REFRESH_ELASTICSEARCH_INDEX,
                handler: refreshElasticsearchIndex,
            },
            {
                exchangeName: 'edlib_new_user',
                handler: newUser,
            },
        ].map((subscriber) => {
            const handler = subscriber.handler({ pubSubConnection });

            return pubsub.subscribe(
                pubSubConnection,
                subscriber.exchangeName,
                subscriber.exchangeName + '-resourceapi_handler',
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
