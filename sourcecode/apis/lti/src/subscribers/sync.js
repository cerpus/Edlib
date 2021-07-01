import * as Sentry from '@sentry/node';
import { buildRawContext } from '../context/index.js';
import moment from 'moment';
import appConfig from '../config/app.js';

const pageSize = 1000;
export default ({ pubSubConnection }) => async ({ jobId }) => {
    const context = buildRawContext({}, {}, { pubSubConnection });

    try {
        const {
            pagination: usagePagination,
        } = await context.services.coreInternal.lti.getAllUsages(1, 0);
        const {
            pagination: usageViewPagination,
        } = await context.services.coreInternal.lti.getAllUsageViews(1, 0);
        const totalCount =
            usagePagination.totalCount + usageViewPagination.totalCount;

        let ltiUsageCount = 0;
        let ltiUsageViewCount = 0;
        let missingResourceCount = 0;

        let run = true;
        let offset = 0;
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
            await context.db.sync.update(jobId, {
                percentDone: Math.floor((ltiUsageCount / totalCount) * 100),
                message: `${ltiUsageCount} of ${usagePagination.totalCount} lti usage, ${ltiUsageViewCount} of ${usageViewPagination.totalCount} lti usage views and ${missingResourceCount} manglende referanser til en ressurs`,
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

                ltiUsageIds.push(ltiUsage.uuid);
            }

            if (bulk.length !== 0) {
                await context.db.usage.createManyOrIgnore(bulk);
            }

            if (ltiUsages.length === 0) {
                run = false;
            }

            offset = offset + pageSize;
        }

        run = true;
        offset = 0;

        while (run) {
            await context.db.sync.update(jobId, {
                percentDone: Math.floor((ltiUsageCount / totalCount) * 100),
                message: `${ltiUsageCount} of ${usagePagination.totalCount} lti usage, ${ltiUsageViewCount} of ${usageViewPagination.totalCount} lti usage views and ${missingResourceCount} manglende referanser til en ressurs`,
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
                    continue;
                }

                if (ltiUsageView.consumerId) {
                    if (!consumers[ltiUsageView.consumerId]) {
                        consumers[
                            ltiUsageView.consumerId
                        ] = await context.db.consumer.create({
                            key: ltiUsageView.consumerId,
                            secret:
                                'Must be updated!! (j afikljsp385094uq pfla)',
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
        }

        await context.db.sync.update(jobId, {
            doneAt: new Date(),
            message: `Ferdig med å synkronisere ${ltiUsageCount} "lti usage" og ${ltiUsageViewCount} "lti usage views" og ${missingResourceCount} manglende referanser til en ressurs`,
        });
    } catch (e) {
        console.error(e);
        await context.db.sync.update(jobId, {
            message: e.message.substring(0, 255),
            failedAt: new Date(),
            doneAt: new Date(),
        });
        Sentry.captureException(e);
    }
};
