import * as Sentry from '@sentry/node';
import { buildRawContext } from '../context/index.js';
import moment from 'moment';
import { getResumeData, updateJobInfo } from '../services/job.js';
import JobKilledException from '../exceptions/JobKilledException.js';
import { logger } from '@cerpus/edlib-node-utils';
import { v4 as uuidV4 } from 'uuid';

const pageSize = 1000;

export default ({ pubSubConnection }) => async ({ jobId }) => {
    const context = buildRawContext({}, {}, { pubSubConnection });

    try {
        let resumeData = await getResumeData(context, jobId);

        const {
            pagination: usageViewPagination,
        } = await context.services.coreInternal.lti.getAllUsageViews(1, 0);
        const totalCount = usageViewPagination.totalCount;

        let ltiUsageViewCount = resumeData ? resumeData.ltiUsageViewCount : 0;

        let run = true;
        let offset = resumeData ? resumeData.offset : 0;
        let ltiUsageIds = [];

        let consumers = (await context.db.consumer.getAll()).reduce(
            (consumers, consumer) => ({
                ...consumers,
                [consumer.key]: consumer,
            }),
            {}
        );

        let consumerUsers = (
            await context.db.consumerUser.getAllWithDeprecatedTenantId()
        ).reduce(
            (consumerUsers, consumerUser) =>
                consumerUser.deprecatedTenantId
                    ? {
                          ...consumerUsers,
                          [consumerUser.deprecatedTenantId]: consumerUser,
                      }
                    : consumerUsers,
            {}
        );

        while (run) {
            await updateJobInfo(context, jobId, {
                percentDone: Math.floor((ltiUsageViewCount / totalCount) * 100),
                message: `${ltiUsageViewCount} av ${usageViewPagination.totalCount} lti usage views`,
            });

            const {
                results: ltiUsageViews,
            } = await context.services.coreInternal.lti.getAllUsageViews(
                pageSize,
                offset
            );

            const bulk = [];
            for (let ltiUsageView of ltiUsageViews) {
                ltiUsageViewCount++;
                let consumerId = null;
                let consumerUserId = null;

                if (ltiUsageIds.indexOf(ltiUsageView.resourceUsageId) === -1) {
                    const dbLtiUsage = await context.db.usage.getById(
                        ltiUsageView.resourceUsageId
                    );

                    if (!dbLtiUsage) {
                        continue;
                    }

                    ltiUsageIds.push(dbLtiUsage.id);
                }

                if (ltiUsageView.consumerId) {
                    if (!consumers[ltiUsageView.consumerId]) {
                        consumers[
                            ltiUsageView.consumerId
                        ] = await context.db.consumer.create({
                            key: ltiUsageView.consumerId,
                            secret: `Must be updated!! (${uuidV4()})`,
                        });
                    }
                    consumerId = consumers[ltiUsageView.consumerId].id;
                }

                if (ltiUsageView.tenantId) {
                    if (!consumerUsers[ltiUsageView.tenantId]) {
                        consumerUsers[
                            ltiUsageView.tenantId
                        ] = await context.db.consumerUser.create({
                            consumerId: consumerId,
                            consumerUserId: ltiUsageView.consumerUserId,
                            userId: ltiUsageView.consumerUserExtId,
                            deprecatedTenantId: ltiUsageView.tenantId,
                        });
                    }
                    consumerUserId = consumerUsers[ltiUsageView.tenantId].id;
                }

                bulk.push({
                    id: ltiUsageView.id,
                    usageId: ltiUsageView.resourceUsageId,
                    consumerUserId: consumerUserId,
                    createdAt: moment(ltiUsageView.createdAt).toDate(),
                });
            }

            if (bulk.length !== 0) {
                await context.db.usageView.createManyOrIgnore(bulk);
            }

            if (ltiUsageViews.length === 0) {
                run = false;
            }

            offset = offset + pageSize;

            await updateJobInfo(context, jobId, {
                resumeData: {
                    offset,
                    ltiUsageViewCount,
                },
            });
        }

        await updateJobInfo(context, jobId, {
            doneAt: new Date(),
            message: `Ferdig med å synkronisere ${ltiUsageViewCount} "lti usage views"`,
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
