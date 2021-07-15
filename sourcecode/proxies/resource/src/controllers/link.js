import { validateJoi } from '@cerpus/edlib-node-utils';
import Joi from '@hapi/joi';

export default {
    create: async (req, res, next) => {
        return req.context.services.coreExternal.links.create(
            req.params.resourceId
        );
    },
    createFromUrl: async (req, res, next) => {
        const { url } = validateJoi(
            req.body,
            Joi.object().keys({
                url: Joi.string().uri().required(),
            })
        );

        return req.context.services.coreExternal.links.createFromUrl(url);
    },
};
