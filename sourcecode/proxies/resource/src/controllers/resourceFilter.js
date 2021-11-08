export default {
    getContentTypesForExternalSystemName: async (req, res, next) => {
        const {
            data,
        } = await req.context.services.resource.getContentTypesForExternalSystemName(
            req.params.externalSystemName
        );

        return {
            data: data.map((r) => r.contentType),
        };
    },
    getContentTypesForExternalSystemNameV2: async (req, res, next) => {
        return req.context.services.resource.getContentTypesForExternalSystemName(
            req.params.externalSystemName
        );
    },
};
