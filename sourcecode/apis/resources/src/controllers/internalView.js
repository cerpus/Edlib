import { NotFoundException } from '@cerpus/edlib-node-utils';
import externalSystemService from '../services/externalSystem.js';
import resourceAccessService from '../services/resourceAccess.js';

export default {
    viewTenantResourceInfo: async (req) => {
        let resourceVersion;
        if (req.query.resourceVersionId) {
            resourceVersion = await req.context.db.resourceVersion.getById(
                req.query.resourceVersionId
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

        if (
            !(await resourceAccessService.isResourceVersionViewableByTenant(
                req.context,
                resourceVersion,
                req.params.tenantId
            ))
        ) {
            throw new NotFoundException('resource');
        }

        return externalSystemService.getViewResourceInfo(resourceVersion);
    },
};
