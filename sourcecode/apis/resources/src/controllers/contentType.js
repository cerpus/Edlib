export default {
    getContentTypesForExternalSystemName: async (req) => {
        const contentTypes = (
            await req.context.db.resourceVersion.getContentTypesForExternalSystemName(
                req.params.externalSystemName
            )
        ).map((row) => row.contentType);

        const contentTypeInfo = await Promise.all(
            contentTypes.map(async (contentType) => ({
                contentType,
                result: await req.context.services.externalResourceFetcher.getContentTypeInfo(
                    req.params.externalSystemName,
                    contentType
                ),
            }))
        );

        return {
            data: contentTypeInfo.map(
                (cti) =>
                    cti.result || {
                        externalSystemName: req.params.externalSystemName,
                        group: null,
                        contentType: cti.contentType,
                        title: cti.contentType,
                        icon: null,
                    }
            ),
        };
    },
};
