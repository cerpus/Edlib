import * as Sentry from '@sentry/node';
import { buildRawContext } from '../context/index.js';
import { logger } from '@cerpus/edlib-node-utils';
import apiConfig from '../config/apis.js';
import saveEdlibResourcesAPI from './saveEdlibResourcesAPI.js';
import JobKilledException from '../exceptions/JobKilledException.js';
import { getResumeData, updateJobInfo } from '../services/job.js';

export default ({ pubSubConnection }) =>
    async ({ jobId }) => {
        const context = buildRawContext({}, {}, { pubSubConnection });

        try {
            let resumeData = await getResumeData(context, jobId);

            const syncs = Object.entries(apiConfig.externalResourceAPIS).reduce(
                (syncs, [name, externalApiConfig]) => {
                    if (externalApiConfig.getAllGroups) {
                        syncs.push(
                            ...externalApiConfig.getAllGroups.map(
                                (groupName) => ({
                                    name,
                                    params: {
                                        group: groupName,
                                    },
                                    limit: groupName === 'article' ? 100 : 1000,
                                })
                            )
                        );
                    } else {
                        syncs.push({
                            name,
                            params: {},
                            limit: 1000,
                        });
                    }

                    return syncs;
                },
                []
            );

            const totalResourceCount = (
                await Promise.all(
                    syncs.map(async (syncConfig) => {
                        const { pagination } =
                            await context.services.externalResourceFetcher.getAll(
                                syncConfig.name,
                                { ...syncConfig.params }
                            );

                        return pagination ? pagination.totalCount : 0;
                    })
                )
            ).reduce(
                (totalCount, resourceCount) => totalCount + resourceCount,
                0
            );

            let resourceCount =
                resumeData && resumeData.resourceCount
                    ? resumeData.resourceCount
                    : 0;

            for (let syncConfig of syncs) {
                const key = JSON.stringify(syncConfig);

                if (resumeData && key !== resumeData.stepKey) {
                    continue;
                }

                let run = true;
                const limit = syncConfig.limit;
                let offset = resumeData ? resumeData.offset : 0;
                resumeData = null;

                while (run) {
                    await updateJobInfo(context, jobId, {
                        percentDone: Math.floor(
                            (resourceCount / totalResourceCount) * 100
                        ),
                        message: `Retrieve resources from external systems. ${resourceCount} of ${totalResourceCount} done. Running sync for ${
                            syncConfig.name
                        } with params ${JSON.stringify(syncConfig.params)}`,
                    });

                    const { resources } =
                        await context.services.externalResourceFetcher.getAll(
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

                    await updateJobInfo(context, jobId, {
                        resumeData: {
                            offset,
                            stepKey: key,
                            resourceCount: resourceCount,
                        },
                    });
                }
            }

            await updateJobInfo(context, jobId, {
                doneAt: new Date(),
                message: `Ferdig med å synkronisere ${resourceCount} ekterne ressurser.`,
                resumeData: null,
            });
        } catch (e) {
            await context.db.job.update(jobId, {
                message: e.message,
                failedAt: new Date(),
                doneAt: new Date(),
            });

            if (!(e instanceof JobKilledException)) {
                logger.error(e);
                Sentry.captureException(e);
            }
        }
    };
