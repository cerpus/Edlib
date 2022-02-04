import Joi from '@hapi/joi';
import { validateJoi } from '@cerpus/edlib-node-utils';

const loginValidation = Joi.object().keys({
    code: Joi.string().min(1).required(),
    callbackUrl: Joi.string().min(1).required(),
});

export default {
    loginCallback: async (req, res, next) => {
        const { code, callbackUrl } = validateJoi(req.query, loginValidation);

        return await req.context.services.edlibAuth.cerpusAuthLoginCallback(
            code,
            callbackUrl
        );
    },
    refresh: async (req, res, next) => {
        const { token } = await req.context.services.edlibAuth.refreshToken(
            req.authorizationJwt
        );

        return {
            token,
        };
    },
    convert: async (req, res, next) => {
        const { token } = await req.context.services.edlibAuth.convertToken(
            req.body.externalToken
        );

        return {
            token,
        };
    },
    me: async (req, res, next) => {
        return req.user;
    },
    getAuthServiceInfo: async (req, res, next) => {
        return await req.context.services.edlibAuth.getAuthServiceInfo();
    },
};
