import Sentry from '@sentry/node';
import { buildRawContext } from '../context/index.js';
import { logger } from '@cerpus/edlib-node-utils';
import moment from 'moment';

export default ({ pubSubConnection }) => async ({ jobId }) => {
    const context = buildRawContext({}, {}, { pubSubConnection });

    try {
        let resourceCountFromCore = 0;
        let resourceCount = 0;

        let run = true;
        const limit = 2;
        let offset = 0;

        while (run) {
            const {
                results,
                pagination,
            } = await context.services.coreInternal.url.getAllUrlResources(
                limit,
                offset
            );

            resourceCountFromCore = pagination.totalCount;

            for (let resource of results) {
                resourceCount++;
                const existingDbResource = await context.db.url.getById(
                    resource.uuid
                );

                if (!existingDbResource) {
                    await context.db.url.create({
                        id: resource.uuid,
                        name: resource.name,
                        url: resource.uri,
                        updatedAt: moment(resource.createdAt).toDate(),
                        createdAt: moment(resource.createdAt).toDate(),
                    });
                }
            }

            if (results.length === 0) {
                run = false;
            }

            offset = offset + limit;

            await context.db.sync.update(jobId, {
                percentDone: Math.floor(
                    (resourceCount / resourceCountFromCore) * 100
                ),
                message: `Synced ${resourceCount} of ${resourceCountFromCore} url resources`,
            });
        }

        await context.db.sync.update(jobId, {
            doneAt: new Date(),
            message: `Ferdig med å synkronisere ${resourceCount} url'er`,
        });
    } catch (e) {
        logger.error(e);
        await context.db.sync.update(jobId, {
            message: e.message,
            failedAt: new Date(),
            doneAt: new Date(),
        });
        Sentry.captureException(e);
    }
};
