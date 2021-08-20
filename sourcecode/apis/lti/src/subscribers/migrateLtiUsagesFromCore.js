import * as Sentry from '@sentry/node';
import { buildRawContext } from '../context/index.js';
import appConfig from '../config/app.js';
import { updateJobInfo } from '../services/job.js';
import JobKilledException from '../exceptions/JobKilledException.js';
import { logger } from '@cerpus/edlib-node-utils';
import { getResumeData } from '../services/job.js';

const pageSize = 1000;

export default ({ pubSubConnection }) => async ({ jobId }) => {
    const context = buildRawContext({}, {}, { pubSubConnection });

    try {
        let resumeData = await getResumeData(context, jobId);

        const {
            pagination: usagePagination,
        } = await context.services.coreInternal.lti.getAllUsages(1, 0);

        const totalCount = usagePagination.totalCount;
        let ltiUsageCount = resumeData ? resumeData.ltiUsageCount : 0;
        let missingResourceCount = resumeData
            ? resumeData.missingResourceCount
            : 0;

        let run = true;
        let offset = resumeData ? resumeData.offset : 0;

        let consumers = (await context.db.consumer.getAll()).reduce(
            (consumers, consumer) => ({
                ...consumers,
                [consumer.key]: consumer,
            }),
            {}
        );

        while (run) {
            await updateJobInfo(context, jobId, {
                percentDone: Math.floor((ltiUsageCount / totalCount) * 100),
                message: `${ltiUsageCount} av ${usagePagination.totalCount} lti usage, ${missingResourceCount} manglende referanser til en ressurs`,
            });

            const {
                results: ltiUsages,
            } = await context.services.coreInternal.lti.getAllUsages(
                pageSize,
                offset
            );

            const bulk = [];
            for (let ltiUsage of ltiUsages) {
                // ignore resources with empty ids
                if (
                    !ltiUsage.externalSystemId ||
                    ltiUsage.externalSystemId.length === 0
                ) {
                    continue;
                }

                ltiUsageCount++;
                const edlibResource = await context.services.resource.getResourceFromExternalSystemInfo(
                    ltiUsage.externalSystemName,
                    ltiUsage.externalSystemId
                );

                if (!edlibResource) {
                    missingResourceCount++;
                    continue;
                }

                let consumerId = null;
                if (ltiUsage.consumerKey) {
                    if (!consumers[ltiUsage.consumerKey]) {
                        consumers[
                            ltiUsage.consumerKey
                        ] = await context.db.consumer.create({
                            key: ltiUsage.consumerKey,
                            secret:
                                'Must be updated!! (j afikljsp385094uq pfla)',
                        });
                    }
                    consumerId = consumers[ltiUsage.consumerKey].id;
                }

                bulk.push({
                    id: ltiUsage.uuid,
                    consumerId,
                    resourceId: edlibResource.id,
                    resourceVersionId: appConfig.features.autoUpdateLtiUsage
                        ? null
                        : edlibResource.version.id,
                });
            }

            if (bulk.length !== 0) {
                await context.db.usage.createManyOrIgnore(bulk);
            }

            if (ltiUsages.length === 0) {
                run = false;
            }

            offset = offset + pageSize;

            await updateJobInfo(context, jobId, {
                resumeData: {
                    offset,
                    ltiUsageCount,
                    missingResourceCount,
                },
            });
        }

        await updateJobInfo(context, jobId, {
            doneAt: new Date(),
            message: `Ferdig med å synkronisere ${ltiUsageCount} "lti usage", ${missingResourceCount} manglende referanser til en ressurs`,
            resumeData: null,
        });
    } catch (e) {
        await context.db.job.update(jobId, {
            message: e.message.substring(0, 255),
            failedAt: new Date(),
            doneAt: new Date(),
        });

        if (!(e instanceof JobKilledException)) {
            logger.error(e);
            Sentry.captureException(e);
        }
    }
};
