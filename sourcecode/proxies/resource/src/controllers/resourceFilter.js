export default {
    getContentTypesForExternalSystemName: async (req, res, next) => {
        return req.context.services.resource.getContentTypesForExternalSystemName(
            req.params.externalSystemName
        );
    },
};
