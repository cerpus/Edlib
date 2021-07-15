export default {
    getContentAuthorTypes: async (req, res, next) => {
        return req.context.services.coreExternal.resourceFilters.getContentAuthorTypes();
    },
    getContentTypesForExternalSystemName: async (req, res, next) => {
        return req.context.services.resource.getContentTypesForExternalSystemName(
            req.params.externalSystemName
        );
    },
    getLicenses: async (req, res, next) => {
        return req.context.services.coreExternal.resourceFilters.getLicenses();
    },
    getSources: async (req, res, next) => {
        return req.context.services.coreExternal.resourceFilters.getSources();
    },
    getTags: async (req, res, next) => {
        let count = 10;
        if (req.query.count && !isNaN(req.query.count)) {
            count = parseInt(req.query.count);
        }

        if (req.query.q) {
            return req.context.services.coreExternal.resourceFilters.searchTags(
                req.query.q,
                count
            );
        }

        return req.context.services.coreExternal.resourceFilters.getMostPopularTags(
            count
        );
    },
};
