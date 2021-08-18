import { NotFoundException } from '@cerpus/edlib-node-utils';

export default {
    findCurrentResourceVersion: async (req, res, next) => {
        const resourceId = req.params.resourceId;

        let resourceVersion = await req.context.db.resourceVersion.getLatestPublishedResourceVersion(
            resourceId
        );

        if (!resourceVersion) {
            throw new NotFoundException('resourceVersion');
        }

        return resourceVersion;
    },
    getResourceVersion: async (req, res, next) => {
        const resourceId = req.params.resourceId;
        const resourceVersionId = req.params.resourceVersionId;

        let resourceVersion = await req.context.db.resourceVersion.getById(
            resourceVersionId
        );

        if (!resourceVersion || resourceVersion.resourceId !== resourceId) {
            throw new NotFoundException('resourceVersion');
        }

        return resourceVersion;
    },
};
