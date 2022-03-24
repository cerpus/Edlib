import { NotFoundException } from '@cerpus/edlib-node-utils';
import externalSystemService from '../services/externalSystem.js';
import resourceAccessService from '../services/resourceAccess.js';

export default {
    getResourceLtiInfo: async (req) => {
        let resourceVersion;
        if (req.query.versionId) {
            resourceVersion = await req.context.db.resourceVersion.getById(
                req.query.versionId
            );
        }

        if (!resourceVersion) {
            resourceVersion =
                await req.context.db.resourceVersion.getLatestNonDraftResourceVersion(
                    req.params.resourceId
                );
        }

        if (
            !resourceVersion ||
            resourceVersion.resourceId !== req.params.resourceId ||
            !(await resourceAccessService.isResourceVersionViewable(
                req.context,
                resourceVersion
            ))
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
            resourceVersion =
                await req.context.db.resourceVersion.getLatestResourceVersion(
                    req.params.resourceId
                );
        }

        if (!resourceVersion) {
            throw new NotFoundException('resource');
        }

        if (
            !(await resourceAccessService.isResourceVersionViewableByTenant(
                req.context,
                resourceVersion,
                String(req.params.tenantId)
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
