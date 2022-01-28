import Joi from '@hapi/joi';
import { validateJoi } from '@cerpus/edlib-node-utils';
import { UnauthorizedException, services } from '@cerpus/edlib-node-utils';
import apiConfig from '../config/apis.js';

const loginValidation = Joi.object().keys({
    code: Joi.string().min(1).required(),
    callbackUrl: Joi.string().min(1).required(),
});

export default {
    loginCallback: async (req, res, next) => {
        const { code, callbackUrl } = validateJoi(req.query, loginValidation);

        const authServiceInfo = await req.context.services.auth.loginCallback(
            code,
            callbackUrl
        );

        const { token: externalToken } =
            await req.context.services.auth.generateJwt(
                authServiceInfo.access_token
            );

        return {
            externalToken,
        };
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
        return {
            adapter: apiConfig.externalAuth.adapter,
            settings: apiConfig.externalAuth.adapterSettings.public,
        };
    },
};
