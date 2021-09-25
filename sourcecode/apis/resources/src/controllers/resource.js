import _ from 'lodash';
import {
    NotFoundException,
    pubsub,
    validateJoi,
} from '@cerpus/edlib-node-utils';
import resourceService from '../services/resource.js';
import resourceAccessService from '../services/resourceAccess.js';
import saveEdlibResourcesAPI from '../subscribers/saveEdlibResourcesAPI.js';
import Joi from 'joi';
import pubsubTopics from '../constants/pubsubTopics.js';

export default {
    getResource: async (req, res, next) => {
        const resourceId = req.params.resourceId;

        let resource = await req.context.db.resource.getById(resourceId);

        if (!resource) {
            throw new NotFoundException('resource');
        }

        return resource;
    },
    getResourceFromExternalId: async (req, res, next) => {
        const externalSystemName = req.params.externalSystemName;
        const externalSystemId = req.params.externalSystemId;

        let resourceVersion = await req.context.db.resourceVersion.getByExternalId(
            externalSystemName,
            externalSystemId
        );

        if (!resourceVersion) {
            throw new NotFoundException('resource');
        }

        let resource = await req.context.db.resource.getById(
            resourceVersion.resourceId
        );

        return { ...resource, version: resourceVersion };
    },
    getResourceFromExternalReferences: async (req, res, next) => {
        const { externalSystemReferences } = validateJoi(
            req.body,
            Joi.object().keys({
                externalSystemReferences: Joi.array()
                    .items(
                        Joi.object().keys({
                            externalSystemName: Joi.string().required(),
                            externalSystemId: Joi.string().required(),
                        })
                    )
                    .required(),
            })
        );

        const resources = await Promise.all(
            externalSystemReferences.map(async (externalSystemReference) => {
                let resourceVersion = await req.context.db.resourceVersion.getByExternalId(
                    externalSystemReference.externalSystemName,
                    externalSystemReference.externalSystemId
                );

                if (!resourceVersion) {
                    throw new NotFoundException('resource');
                }

                let resource = await req.context.db.resource.getById(
                    resourceVersion.resourceId
                );

                return { ...resource, version: resourceVersion };
            })
        );

        return {
            resources,
        };
    },
    getPublicResources: async (req) => {
        return resourceService.getResourcesFromRequest(req, null);
    },
    getTenantResources: async (req) => {
        return resourceService.getResourcesFromRequest(
            req,
            req.params.tenantId
        );
    },
    deleteTenantResource: async (req) => {
        const resource = await req.context.db.resource.getById(
            req.params.resourceId
        );

        if (
            !resource ||
            !(await resourceAccessService.hasResourceAccess(
                req.context,
                resource,
                req.params.tenantId
            ))
        ) {
            throw new NotFoundException('resource');
        }

        await req.context.services.elasticsearch.remove(resource.id);

        return req.context.db.resource.update(resource.id, {
            deletedAt: new Date(),
        });
    },
    ensureResourceExists: async (req) => {
        const externalResource = await req.context.services.externalResourceFetcher.getById(
            req.params.externalSystemName,
            req.params.externalSystemId
        );

        await saveEdlibResourcesAPI({
            pubSubConnection: req.context.pubSubConnection,
        })(externalResource, true, true);

        return await req.context.db.resourceVersion.getByExternalId(
            req.params.externalSystemName,
            req.params.externalSystemId
        );
    },
    setContextResourceCollaborators: async (req) => {
        let {
            applicationId,
            context,
            resourceIds,
            tenantIds,
            externalResources,
        } = validateJoi(
            req.body,
            Joi.object().keys({
                applicationId: Joi.string().guid().required(),
                context: Joi.string().required(),
                resourceIds: Joi.array().items(Joi.string()).required(),
                tenantIds: Joi.array().items(Joi.string()).required(),
                externalResources: Joi.array()
                    .items(
                        Joi.object().keys({
                            systemName: Joi.string().required(),
                            resourceId: Joi.string().required(),
                        })
                    )
                    .optional(),
            })
        );

        if (!resourceIds) {
            resourceIds = [];
        }

        const existingResourceCollaborators = await req.context.db.resourceCollaborator.getforApplicationContext(
            applicationId,
            context
        );

        if (externalResources) {
            const resourceVersions = (
                await Promise.all(
                    externalResources.map((er) =>
                        req.context.db.resourceVersion.getByExternalId(
                            er.systemName,
                            er.resourceId
                        )
                    )
                )
            ).filter(Boolean);

            resourceIds = [
                ...resourceIds,
                ...resourceVersions.map((rv) => rv.resourceId),
            ];
        }

        resourceIds = _.uniq(resourceIds);

        const rows = resourceIds.reduce((rows, resourceId) => {
            const newRows = tenantIds.map((tenantId) => ({
                applicationId,
                context,
                resourceId,
                tenantId,
            }));
            return [...rows, ...newRows];
        }, []);

        // rows to create
        await Promise.all(
            rows
                .filter(
                    (row) =>
                        !existingResourceCollaborators.some(
                            (erc) =>
                                erc.resourceId === row.resourceId &&
                                erc.tenantId === row.tenantId
                        )
                )
                .map((row) => req.context.db.resourceCollaborator.create(row))
        );

        const toRemove = existingResourceCollaborators.filter(
            (erc) =>
                !rows.some(
                    (row) =>
                        erc.resourceId === row.resourceId &&
                        erc.tenantId === row.tenantId
                )
        );

        if (toRemove.length !== 0) {
            await req.context.db.resourceCollaborator.remove(
                toRemove.map((erc) => erc.id)
            );
        }

        await Promise.all(
            rows.map((row) =>
                pubsub.publish(
                    req.context.pubSubConnection,
                    pubsubTopics.UPDATE_ELASTICSEARCH_FOR_RESOURCE,
                    JSON.stringify({
                        resourceId: row.resourceId,
                    })
                )
            )
        );

        await Promise.all(
            toRemove.map((remove) =>
                pubsub.publish(
                    req.context.pubSubConnection,
                    pubsubTopics.UPDATE_ELASTICSEARCH_FOR_RESOURCE,
                    JSON.stringify({
                        resourceId: remove.resourceId,
                    })
                )
            )
        );

        return {
            success: true,
        };
    },
};
