export default {
    getContentTypesForExternalSystemName: async (req) => {
        return {
            data: (
                await req.context.db.resourceVersion.getContentTypesForExternalSystemName(
                    req.params.externalSystemName
                )
            ).map((row) => row.contentType),
        };
    },
};
