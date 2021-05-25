import { NotFoundException } from '@cerpus/edlib-node-utils/exceptions/index.js';
import resourceService from '../services/resource.js';
import saveEdlibResourcesAPI from '../subscribers/saveEdlibResourcesAPI.js';

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
            !(await resourceService.hasResourceWriteAccess(
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
};
