import Joi from '@hapi/joi';
import { validateJoi } from '@cerpus/edlib-node-utils';

const loginValidation = Joi.object().keys({
    code: Joi.string().min(1).required(),
    callbackUrl: Joi.string().min(1).required(),
});

const setCookie = (req, res, token, expiresAt) => {
    res.cookie('jwt', token, {
        expires: new Date(expiresAt * 1000),
        httpOnly: true,
        sameSite: 'none',
        secure: true,
        domain: req.host.replace('api.', ''),
    });
};

export default {
    loginCallback: async (req, res, next) => {
        const { code, callbackUrl } = validateJoi(req.query, loginValidation);

        return await req.context.services.edlibAuth.cerpusAuthLoginCallback(
            code,
            callbackUrl
        );
    },
    refresh: async (req, res, next) => {
        const { token, expiresAt } =
            await req.context.services.edlibAuth.refreshToken(
                req.authorizationJwt
            );

        setCookie(req, res, token, expiresAt);
        return {
            token,
            expiresAt,
        };
    },
    convert: async (req, res, next) => {
        const { token, expiresAt } =
            await req.context.services.edlibAuth.convertToken(
                req.body.externalToken
            );

        setCookie(req, res, token, expiresAt);

        return {
            token,
            expiresAt,
        };
    },
    me: async (req, res, next) => {
        return req.user;
    },
    getAuthServiceInfo: async (req, res, next) => {
        return await req.context.services.edlibAuth.getAuthServiceInfo();
    },
};
