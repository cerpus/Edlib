import { setupApi, pubsub } from '@cerpus/edlib-node-utils';
import router from './routes/index.js';
import errorReportingConfig from './config/errorReporting.js';
import saveEdlibResourcesAPI from './subscribers/saveEdlibResourcesAPI.js';
import refreshElasticsearchIndex from './subscribers/refreshElasticsearchIndex.js';
import newUser from './subscribers/newUser.js';
import { buildRawContext } from './context/index.js';
import jobNames from './constants/jobNames.js';
import saveTrackingResourceVersion from './subscribers/saveTrackingResourceVersion.js';
import syncLtiUsageViews from './subscribers/syncLtiUsageViews.js';
import syncCoreIds from './subscribers/syncCoreIds.js';
import syncExternalResources from './subscribers/syncExternalResources.js';
import updateElasticsearchForResource from './subscribers/updateElasticsearchForResource.js';
import pubsubTopics from './constants/pubsubTopics.js';

const internalJobsPrefix = '__internal_edlibResource_jobs_';

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
                    internalJobsPrefix + jobNames.REFRESH_ELASTICSEARCH_INDEX,
                handler: refreshElasticsearchIndex,
            },
            {
                exchangeName:
                    internalJobsPrefix + jobNames.SYNC_LTI_USAGE_VIEWS,
                handler: syncLtiUsageViews,
            },
            {
                exchangeName: internalJobsPrefix + jobNames.SYNC_CORE_IDS,
                handler: syncCoreIds,
            },
            {
                exchangeName:
                    internalJobsPrefix + jobNames.SYNC_EXTERNAL_RESOURCES,
                handler: syncExternalResources,
            },
            {
                exchangeName: 'edlib_new_user',
                handler: newUser,
            },
            {
                exchangeName: 'edlib_trackingResourceVersion',
                handler: saveTrackingResourceVersion,
            },
            {
                exchangeName: pubsubTopics.UPDATE_ELASTICSEARCH_FOR_RESOURCE,
                handler: updateElasticsearchForResource,
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
