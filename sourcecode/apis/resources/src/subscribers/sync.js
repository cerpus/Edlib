import Sentry from '@sentry/node';
import { buildRawContext } from '../context/index.js';
import apiConfig from '../config/apis.js';
import saveEdlibResourcesAPI from './saveEdlibResourcesAPI.js';
import * as elasticSearchService from '../services/elasticSearch.js';
import resourceService from '../services/resource.js';
import { logger } from '@cerpus/edlib-node-utils/index.js';

export default ({ pubSubConnection }) => async ({ jobId }) => {
    const context = buildRawContext({}, {}, { pubSubConnection });
    const numberOfSteps = 3;

    try {
        const syncs = Object.entries(apiConfig.externalResourceAPIS).reduce(
            (syncs, [name, externalApiConfig]) => {
                if (externalApiConfig.getAllGroups) {
                    syncs.push(
                        ...externalApiConfig.getAllGroups.map((groupName) => ({
                            name,
                            params: {
                                group: groupName,
                            },
                        }))
                    );
                } else {
                    syncs.push({
                        name,
                        params: {},
                    });
                }

                return syncs;
            },
            []
        );

        const totalResourceCount = (
            await Promise.all(
                syncs.map(async (syncConfig) => {
                    const {
                        pagination,
                    } = await context.services.externalResourceFetcher.getAll(
                        syncConfig.name,
                        { ...syncConfig.params }
                    );

                    return pagination ? pagination.totalCount : 0;
                })
            )
        ).reduce((totalCount, resourceCount) => totalCount + resourceCount, 0);

        let resourceCount = 0;
        let coreSyncCount = 0;
        let elasticsearchSyncCount = 0;

        for (let syncConfig of syncs) {
            let run = true;
            const limit = 10;
            let offset = 0;

            while (run) {
                await context.db.sync.update(jobId, {
                    percentDone: Math.floor(
                        (resourceCount / totalResourceCount / numberOfSteps) *
                            100
                    ),
                    message: `Step 1, retrieve resources from external systems. ${resourceCount} of ${totalResourceCount} done. Running sync for ${
                        syncConfig.name
                    } with params ${JSON.stringify(syncConfig.params)}`,
                });

                const {
                    resources,
                } = await context.services.externalResourceFetcher.getAll(
                    syncConfig.name,
                    { offset: offset, limit, ...syncConfig.params }
                );

                for (let resource of resources) {
                    resourceCount++;
                    await saveEdlibResourcesAPI({ pubSubConnection })(
                        resource,
                        false
                    );
                }

                if (resources.length === 0) {
                    run = false;
                }

                offset = offset + limit;
            }
        }

        {
            // sync ids from core lti_resources
            let run = true;
            const limit = 50;
            let offset = 0;
            while (run) {
                await context.db.sync.update(jobId, {
                    percentDone: Math.floor(
                        (coreSyncCount / totalResourceCount / numberOfSteps) *
                            100 +
                            100 / 3
                    ),
                    message: `Step 2, sync resources with core. ${coreSyncCount} of ${totalResourceCount} done.`,
                });

                const resourceVersions = await context.db.resourceVersion.getAllPaginated(
                    offset,
                    limit
                );

                await resourceService.retrieveCoreInfo(
                    context,
                    resourceVersions
                );

                if (resourceVersions.length === 0) {
                    run = false;
                }

                coreSyncCount = coreSyncCount + resourceVersions.length;
                offset = offset + limit;
            }
        }
        {
            // save all resources to local elasticsearch instance
            let run = true;
            const limit = 50;
            let offset = 0;
            while (run) {
                await context.db.sync.update(jobId, {
                    percentDone: Math.floor(
                        (elasticsearchSyncCount /
                            totalResourceCount /
                            numberOfSteps) *
                            100 +
                            (100 / 3) * 2
                    ),
                    message: `Step 3, sync resource version with elasticsearch. ${elasticsearchSyncCount} of ${totalResourceCount} done.`,
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
                elasticsearchSyncCount =
                    elasticsearchSyncCount + resources.length;
            }
        }

        await context.db.sync.update(jobId, {
            doneAt: new Date(),
            message: `Ferdig med å synkronisere ${resourceCount} ressurser.`,
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
