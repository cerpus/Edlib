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

        const {
            token: externalToken,
        } = await req.context.services.auth.generateJwt(
            authServiceInfo.access_token
        );

        const user = await req.context.services.auth.identity(
            authServiceInfo.access_token
        );

        const {
            token: internalToken,
        } = await req.context.services.edlibAuth.convertToken(externalToken);

        return {
            user,
            token: internalToken,
        };
    },
    refresh: async (req, res, next) => {
        const refreshToken = req.query.refresh_token;
        if (!refreshToken) {
            throw new UnauthorizedException();
        }

        let refreshTokenPayload;

        try {
            const { payload } = services.jwt.verify(refreshToken);
            refreshTokenPayload = payload;
        } catch (e) {
            throw new UnauthorizedException();
        }

        const { token } = await req.context.services.auth.generateJwt(
            refreshTokenPayload.access_token
        );

        return {
            authToken: token,
        };
    },
    refreshV2: async (req, res, next) => {
        const { token } = await req.context.services.auth.refreshJwt(
            req.authorizationJwt
        );

        return {
            authToken: token,
        };
    },
    refreshV3: async (req, res, next) => {
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
    logout: async (req, res, next) => {
        services.cookie.clearCookie(res, 'jwt');
        res.sendStatus(200);
    },
    me: async (req, res, next) => {
        return req.user;
    },
    getAuthServiceInfo: async (req, res, next) => {
        return {
            url: apiConfig.externalAuth.url,
            clientId: apiConfig.externalAuth.clientId,
        };
    },
};
