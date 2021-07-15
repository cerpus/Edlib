import { fromCore } from '../services/resourceConverter.js';

export default {
    get: async (req, res, next) => {
        const recommendations = await req.context.services.coreExternal.recommendations.get(
            req.body
        );

        return {
            ...recommendations,
            data: await fromCore(req.context, recommendations.data),
        };
    },
    getV2: async (req, res, next) => {
        if (req.query.contentFilter === 'myContent') {
            return await req.context.services.resource.getTenantResources(
                req.user.identityId,
                req.query
            );
        }

        return await req.context.services.resource.getPublicResources(
            req.query
        );
    },
};
