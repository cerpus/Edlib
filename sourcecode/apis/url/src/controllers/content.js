export default {
    getEdlibResourceContent: async (req) => {
        return {
            resources: (
                await req.context.db.url.getAll(
                    req.query.limit,
                    req.query.offset
                )
            ).map((dbUrl) => ({
                externalSystemName: 'url',
                externalSystemId: dbUrl.id,
                title: dbUrl.name,
                ownerId: 'all',
                isPublished: true,
                isListed: true,
                language: null,
                updatedAt: dbUrl.updatedAt,
                createdAt: dbUrl.createdAt,
            })),
        };
    },
};
