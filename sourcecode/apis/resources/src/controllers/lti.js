import { NotFoundException } from '@cerpus/edlib-node-utils/exceptions/index.js';
import externalSystemService from '../services/externalSystem.js';
import resourceService from '../services/resource.js';

export default {
    getResourceLtiInfo: async (req) => {
        if (
            !(await resourceService.isPublished(
                req.context,
                req.params.resourceId
            ))
        ) {
            throw new NotFoundException('resource');
        }

        let resourceVersion;
        if (req.query.versionId) {
            resourceVersion = await req.context.db.resourceVersion.getById(
                req.query.versionId
            );
        }

        if (!resourceVersion) {
            resourceVersion = await req.context.db.resourceVersion.getLatestNonDraftResourceVersion(
                req.params.resourceId
            );
        }

        if (
            !resourceVersion ||
            resourceVersion.resourceId !== req.params.resourceId
        ) {
            throw new NotFoundException('resource');
        }

        return externalSystemService.getLtiResourceInfo(resourceVersion);
    },
    getTenantResourceLtiInfo: async (req) => {
        let resourceVersion;
        if (req.query.versionId) {
            resourceVersion = await req.context.db.resourceVersion.getById(
                req.query.versionId
            );
        }

        if (!resourceVersion) {
            resourceVersion = await req.context.db.resourceVersion.getLatestNonDraftResourceVersion(
                req.params.resourceId
            );
        }

        if (!resourceVersion) {
            throw new NotFoundException('resource');
        }

        const resource = await req.context.db.resource.getById(
            resourceVersion.resourceId
        );

        if (!resource) {
            throw new NotFoundException('resource');
        }

        const resourceStatus = await resourceService.status(
            req.context,
            req.params.resourceId
        );

        const canGet = async () => {
            if (resourceStatus.isPublished && resourceStatus.isListed) {
                return true;
            }

            const hasWriteAccess = await resourceService.hasResourceWriteAccess(
                req.context,
                resource,
                req.params.tenantId
            );

            if (hasWriteAccess) {
                return true;
            }

            return false;
        };

        if (!(await canGet())) {
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
