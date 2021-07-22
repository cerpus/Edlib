export default {
    getV2: async (req, res, next) => {
        if (req.query.contentFilter === 'myContent') {
            return await req.context.services.resource.getTenantResources(
                req.user.identityId,
                req.query
            );
        }

        return await req.context.services.resource.getPublicResources(
            req.query
        );
    },
};
