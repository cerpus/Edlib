import { runSubscribers } from '@cerpus/edlib-node-utils';
import saveEdlibResourcesAPI from './subscribers/saveEdlibResourcesAPI.js';
import jobNames from './constants/jobNames.js';
import refreshElasticsearchIndex from './subscribers/refreshElasticsearchIndex.js';
import syncLtiUsageViews from './subscribers/syncLtiUsageViews.js';
import syncCoreIds from './subscribers/syncCoreIds.js';
import syncExternalResources from './subscribers/syncExternalResources.js';
import newUser from './subscribers/newUser.js';
import saveTrackingResourceVersion from './subscribers/saveTrackingResourceVersion.js';
import pubsubTopics from './constants/pubsubTopics.js';
import updateElasticsearchForResource from './subscribers/updateElasticsearchForResource.js';

const internalJobsPrefix = '__internal_edlibResource_jobs_';

runSubscribers(
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
            exchangeName: internalJobsPrefix + jobNames.SYNC_LTI_USAGE_VIEWS,
            handler: syncLtiUsageViews,
        },
        {
            exchangeName: internalJobsPrefix + jobNames.SYNC_CORE_IDS,
            handler: syncCoreIds,
        },
        {
            exchangeName: internalJobsPrefix + jobNames.SYNC_EXTERNAL_RESOURCES,
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
    ].map((subscriber) => ({
        ...subscriber,
        subscriptionName: subscriber.exchangeName + '-resourceapi_handler',
    }))
);
