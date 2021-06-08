import { NotFoundException } from '@cerpus/edlib-node-utils/exceptions/index.js';
import externalSystemService from '../services/externalSystem.js';
import resourceService from '../services/resource.js';

export default {
    getResourceLtiInfo: async (req) => {
        let resourceVersion;
        if (req.query.versionId) {
            resourceVersion = await req.context.db.resourceVersion.getById(
                req.query.versionId
            );
        }

        if (!resourceVersion) {
            resourceVersion = await req.context.db.resourceVersion.getLatestPublishedResourceVersion(
                req.params.resourceId
            );
        }

        if (
            !resourceVersion ||
            resourceVersion.resourceId !== req.params.resourceId
        ) {
            throw new NotFoundException('resource');
        }

        if (
            req.params.tenantId &&
            !(await resourceService.hasResourceVersionAccess(
                req.context,
                resourceVersion,
                req.params.tenantId
            ))
        ) {
            throw new NotFoundException('resource');
        }

        return externalSystemService.getLtiResourceInfo(resourceVersion);
    },
    getLtiCreateInfo: async (req) => {
        return externalSystemService.getLtiCreateInfo(
            req.params.externalSystemName,
            req.query.group
        );
    },
};
