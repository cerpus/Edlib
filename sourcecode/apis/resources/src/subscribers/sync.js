import Sentry from '@sentry/node';
import { buildRawContext } from '../context/index.js';
import apiConfig from '../config/apis.js';
import saveEdlibResourcesAPI from './saveEdlibResourcesAPI.js';
import * as elasticSearchService from '../services/elasticSearch.js';

export default ({ pubSubConnection }) => async ({ jobId }) => {
    const context = buildRawContext({}, {}, { pubSubConnection });

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

        const percentPerConfig = Math.floor(100 / syncs.length);
        let resourceCount = 0;

        for (let [index, syncConfig] of syncs.entries()) {
            await context.db.sync.update(jobId, {
                percentDone: percentPerConfig * index,
                message: `Running sync for ${
                    syncConfig.name
                } with params ${JSON.stringify(syncConfig.params)}`,
            });

            let run = true;
            const limit = 100;
            let offset = 0;

            while (run) {
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

        let run = true;
        const limit = 100;
        let offset = 0;
        while (run) {
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
        }

        await context.db.sync.update(jobId, {
            doneAt: new Date(),
            message: `Ferdig med å synkronisere ${resourceCount} ressurser`,
        });
    } catch (e) {
        await context.db.sync.update(jobId, {
            message: e.message,
            failedAt: new Date(),
            doneAt: new Date(),
        });
        Sentry.captureException(e);
    }
};
