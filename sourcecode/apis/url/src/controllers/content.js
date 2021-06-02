export default {
    getEdlibResourceContent: async (req) => {
        const limit = req.query.limit || 100;
        const offset = req.query.offset || 0;

        const { count } = await req.context.db.url.count();

        return {
            pagination: {
                totalCount: count,
                limit,
                offset,
            },
            resources: (await req.context.db.url.getAll(limit, offset)).map(
                (dbUrl) => ({
                    externalSystemName: 'url',
                    externalSystemId: dbUrl.id,
                    title: dbUrl.name,
                    ownerId: null,
                    isPublished: true,
                    isListed: true,
                    language: null,
                    updatedAt: dbUrl.updatedAt,
                    createdAt: dbUrl.createdAt,
                })
            ),
        };
    },
};
