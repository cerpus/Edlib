import Joi from '@hapi/joi';
import { validateJoi } from '@cerpus/edlib-node-utils/services/index.js';

export default {
    viewDoku: async (req, res, next) => {
        const { token } = validateJoi(
            req.query,
            Joi.object().keys({
                token: Joi.string().min(1).required(),
            })
        );

        const payload = await req.context.services.coreExternal.doku.getLaunchData(
            token
        );

        const dokuId = payload.cerpus.doku_id;

        return {
            doku: await req.context.services.doku.getById(dokuId),
            ltiLaunch: payload,
        };
    },
};
