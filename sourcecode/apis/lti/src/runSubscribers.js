import { runSubscribers } from '@cerpus/edlib-node-utils';
import jobNames from './constants/jobNames.js';
import importUsages from './subscribers/importUsages.js';

const internalJobsPrefix = '__internal_edlibLti_jobs_';

runSubscribers(
    [
        {
            exchangeName: internalJobsPrefix + jobNames.IMPORT_USAGES,
            handler: importUsages,
        },
    ].map((subscriber) => ({
        ...subscriber,
        subscriptionName: subscriber.exchangeName + '-lti_handler',
    }))
);
