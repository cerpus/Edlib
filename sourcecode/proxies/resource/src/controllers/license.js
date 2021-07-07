export default {
    getAll: async (req, res, next) => {
        return req.context.services.license.getAll();
    },
};
