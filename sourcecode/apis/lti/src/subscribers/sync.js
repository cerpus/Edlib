import * as Sentry from '@sentry/node';
import { buildRawContext } from '../context/index.js';
import moment from 'moment';

export default ({ pubSubConnection }) => async ({ jobId }) => {
    const context = buildRawContext({}, {}, { pubSubConnection });

    try {
        let ltiUsageCount = 0;
        let ltiUsageViewCount = 0;

        const limit = 100;

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
            const ltiUsages = await context.services.coreInternal.lti.getAllUsages(
                limit,
                offset
            );

            for (let ltiUsage of ltiUsages) {
                ltiUsageCount++;
                const edlibResource = await context.services.resource.getResourceFromExternalSystemInfo(
                    ltiUsage.externalSystemName,
                    ltiUsage.externalSystemId
                );

                if (!edlibResource) {
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

                await context.db.usage.createOrUpdate({
                    id: ltiUsage.uuid,
                    consumerId,
                    resourceId: edlibResource.id,
                    resourceVersionId: edlibResource.version.id,
                });

                ltiUsageIds.push(ltiUsage.uuid);
            }

            if (ltiUsages.length === 0) {
                run = false;
            }

            offset = offset + limit;
        }

        run = true;
        offset = 0;

        while (run) {
            const ltiUsageViews = await context.services.coreInternal.lti.getAllUsageViews(
                limit,
                offset
            );

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

                await context.db.usageView.createOrUpdate({
                    id: ltiUsageView.id,
                    usageId: ltiUsageView.resourceUsageId,
                    consumerUserId: consumerUserId,
                    createdAt: moment(ltiUsageView.createdAt).toDate(),
                });
            }

            if (ltiUsageViews.length === 0) {
                run = false;
            }

            offset = offset + limit;
        }

        await context.db.sync.update(jobId, {
            doneAt: new Date(),
            message: `Ferdig med å synkronisere ${ltiUsageCount} "lti usage" og ${ltiUsageViewCount} "lti usage views"`,
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
