import * as Sentry from '@sentry/node';
import { buildRawContext } from '../context/index.js';
import { logger, NotFoundException } from '@cerpus/edlib-node-utils';
import moment from 'moment';

export default ({ pubSubConnection }) => async ({ jobId }) => {
    const context = buildRawContext({}, {}, { pubSubConnection });

    try {
        const { pagination } = await context.services.lti.getUsageViews();
        let totalCount = pagination.count;
        let usageViewCount = 0;

        {
            // save all resources to local elasticsearch instance
            let run = true;
            const limit = 50;
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
                    limit
                );

                for (let usageView of usageViews) {
                    const trackingResourceVersion = await context.db.trackingResourceVersion.getByExternalReference(
                        usageView.id
                    );

                    if (trackingResourceVersion) {
                        continue;
                    }

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

                    await context.db.trackingResourceVersion.create({
                        externalReference: usageView.id,
                        resourceVersionId,
                        createdAt: moment(usageView.createdAt).toDate(),
                    });
                }

                if (usageViews.length === 0) {
                    run = false;
                }

                offset = offset + limit;
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
