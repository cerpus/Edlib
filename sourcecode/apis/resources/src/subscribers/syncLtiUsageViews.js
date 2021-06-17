import * as Sentry from '@sentry/node';
import { buildRawContext } from '../context/index.js';
import { logger, NotFoundException } from '@cerpus/edlib-node-utils';
import moment from 'moment';

const pageSize = 1000;
export default ({ pubSubConnection }) => async ({ jobId }) => {
    const context = buildRawContext({}, {}, { pubSubConnection });

    try {
        const { pagination } = await context.services.lti.getUsageViews();
        let totalCount = pagination.count;
        let usageViewCount = 0;

        {
            // save all resources to local elasticsearch instance
            let run = true;
            let offset = 0;
            while (run) {
                await context.db.job.update(jobId, {
                    percentDone: Math.floor(
                        (usageViewCount / totalCount) * 100
                    ),
                    message: `${usageViewCount} of ${totalCount} done.`,
                });

                const { usageViews } = await context.services.lti.getUsageViews(
                    offset,
                    pageSize,
                    true
                );

                const batch = [];

                for (let usageView of usageViews) {
                    let resourceVersionId = usageView.resourceVersionId;

                    if (!resourceVersionId) {
                        const resourceVersion = await context.db.resourceVersion.getLatestNonDraftResourceVersion(
                            usageView.resourceId
                        );

                        if (!resourceVersion) {
                            throw new NotFoundException('resourceVersion');
                        }

                        resourceVersionId = resourceVersion.id;
                    }

                    batch.push({
                        externalReference: usageView.id,
                        resourceVersionId,
                        createdAt: moment(usageView.createdAt).toDate(),
                    });
                }

                if (batch.length !== 0) {
                    await context.db.trackingResourceVersion.createManyOrIgnore(
                        batch
                    );
                }

                if (usageViews.length === 0) {
                    run = false;
                }

                offset = offset + pageSize;
                usageViewCount = usageViewCount + usageViews.length;
            }
        }

        await context.db.job.update(jobId, {
            doneAt: new Date(),
            message: `Ferdig med å synkronisere ${usageViewCount} lti usage views.`,
        });
    } catch (e) {
        logger.error(e);
        await context.db.job.update(jobId, {
            message: e.message,
            failedAt: new Date(),
            doneAt: new Date(),
        });
        Sentry.captureException(e);
    }
};
