export default {
    removeV2: async (req, res, next) => {
        await req.context.services.resource.deleteResource(
            req.user.id,
            req.params.resourceId
        );

        return {
            success: true,
        };
    },
    getAllAdmin: async (req, res, next) => {
        return {
            resources: await req.context.services.resource.adminGetAllResources(),
        };
    },
    getStats: async (req, res, next) => {
        return await req.context.services.resource.getResourceStats(
            req.params.resourceId,
            req.query
        )
    },
};
