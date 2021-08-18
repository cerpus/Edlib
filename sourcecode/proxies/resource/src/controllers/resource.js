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
};
