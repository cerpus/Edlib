import { validateJoi, apiClients } from '@cerpus/edlib-node-utils';
import externalAuthService from '../services/externalAuth.js';
import externalTokenVerifierConfig from '../config/externalTokenVerifier.js';
import Joi from 'joi';

const loginValidation = Joi.object().keys({
    code: Joi.string().min(1).required(),
    callbackUrl: Joi.string().min(1).required(),
});

export default {
    cerpusAuthLoginCallback: async (req, res, next) => {
        const { code, callbackUrl } = validateJoi(req.query, loginValidation);

        const config = externalAuthService.getConfiguration(
            externalTokenVerifierConfig
        );

        const authClient = apiClients.auth(req, {
            url: config.settings.url,
            clientId: config.settings.clientId,
            secret: config.settings.secret,
        });

        const authServiceInfo = await authClient.loginCallback(
            code,
            callbackUrl
        );

        const { token: externalToken } = await authClient.generateJwt(
            authServiceInfo.access_token
        );

        return {
            externalToken,
        };
    },
    getAuthServiceInfo: async (req, res, next) => {
        const config = externalAuthService.getConfiguration(
            externalTokenVerifierConfig
        );

        return {
            adapter: config.settings.adapter,
            settings: config.frontendSettings,
        };
    },
};
