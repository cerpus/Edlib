import { NotFoundException } from '@cerpus/edlib-node-utils/exceptions/index.js';
import externalSystemService from '../services/externalSystem.js';

export default {
    getResourceLtiInfo: async (req) => {
        let resourceVersion;
        if (req.query.version) {
            resourceVersion = await req.context.db.resourceVersion.getById(
                req.query.version
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

        return externalSystemService.getLtiResourceInfo(resourceVersion);
    },
    getLtiCreateInfo: async (req) => {
        return externalSystemService.getLtiCreateInfo(
            req.params.externalSystemName,
            req.query.group
        );
    },
};
