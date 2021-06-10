import Sentry from '@sentry/node';
import { buildRawContext } from '../context/index.js';
import * as elasticSearchService from '../services/elasticSearch.js';
import { logger } from '@cerpus/edlib-node-utils';

export default ({ pubSubConnection }) => async ({ jobId }) => {
    const context = buildRawContext({}, {}, { pubSubConnection });

    try {
        let totalResourceCount = await context.db.resource.count();
        let resourceCount = 0;

        {
            // save all resources to local elasticsearch instance
            let run = true;
            const limit = 50;
            let offset = 0;
            while (run) {
                await context.db.job.update(jobId, {
                    percentDone: Math.floor(
                        (resourceCount / totalResourceCount) * 100
                    ),
                    message: `${resourceCount} of ${totalResourceCount} done.`,
                });

                const resources = await context.db.resource.getAllPaginated(
                    offset,
                    limit
                );

                for (let resource of resources) {
                    await elasticSearchService.syncResource(context, resource);
                }

                if (resources.length === 0) {
                    run = false;
                }

                offset = offset + limit;
                resourceCount = resourceCount + resources.length;
            }
        }

        await context.db.job.update(jobId, {
            doneAt: new Date(),
            message: `Ferdig med å synkronisere ${resourceCount} ressurser med elasticsearch.`,
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
