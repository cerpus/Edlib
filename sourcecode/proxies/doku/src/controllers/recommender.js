export default {
    indexAll: async (req, res, next) => {
        return req.context.services.doku.indexAllForRecommender();
    },
};
