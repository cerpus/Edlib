export default {
    getById: async (req, res, next) => {
        return req.context.services.doku.getForUser(
            req.user.identityId,
            req.params.dokuId
        );
    },
    create: async (req, res, next) => {
        return req.context.services.doku.createForUser(req.user.identityId, {
            ...req.body,
        });
    },
    update: async (req, res, next) => {
        return req.context.services.doku.updateForUser(
            req.user.identityId,
            req.params.dokuId,
            req.body
        );
    },
    publish: async (req, res, next) => {
        return req.context.services.doku.publishForUser(
            req.user.identityId,
            req.params.dokuId,
            req.body
        );
    },
    unpublish: async (req, res, next) => {
        return req.context.services.doku.unpublishForUser(
            req.user.identityId,
            req.params.dokuId,
            req.body
        );
    },
};
