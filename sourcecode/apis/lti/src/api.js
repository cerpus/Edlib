import { setupApi } from '@cerpus/edlib-node-utils';
import router from './routes/index.js';
import errorReportingConfig from './config/errorReporting.js';
import { pubsub } from '@cerpus/edlib-node-utils';
import logLtiUsageView from './subscribers/logLtiUsageView.js';
import jobNames from './constants/jobNames.js';
import migrateLtiUsagesFromCore from './subscribers/migrateLtiUsagesFromCore.js';
import migrateLtiUsageViewsFromCore from './subscribers/migrateLtiUsageViewsFromCore.js';

const start = async () => {
    const pubSubConnection = await pubsub.setup();

    await Promise.all(
        [
            {
                exchangeName: 'edlib_ltiUsageView',
                handler: logLtiUsageView,
            },
            {
                exchangeName:
                    '__internal_edlibLti_jobs_' +
                    jobNames.MIGRATE_LTI_USAGES_FROM_CORE,
                handler: migrateLtiUsagesFromCore,
            },
            {
                exchangeName:
                    '__internal_edlibLti_jobs_' +
                    jobNames.MIGRATE_LTI_USAGE_VIEWS_FROM_CORE,
                handler: migrateLtiUsageViewsFromCore,
            },
        ].map((subscriber) => {
            const handler = subscriber.handler({ pubSubConnection });

            return pubsub.subscribe(
                pubSubConnection,
                subscriber.exchangeName,
                subscriber.exchangeName + '-apiLtiHandler',
                async (msg) => {
                    await handler(JSON.parse(msg.content));
                }
            );
        })
    );

    setupApi(async () => router({ pubSubConnection }), {
        errorReportingConfig,
    });
};

start();
