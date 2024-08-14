import { logger, validateJoi, apiClients } from '@cerpus/edlib-node-utils';
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

        logger.info('Getting external auth service configuration');
        const config = externalAuthService.getConfiguration(
            externalTokenVerifierConfig
        );
        logger.info('Got external auth service configuration');

        const authClient = apiClients.auth(req, {
            url: config.settings.url,
            clientId: config.settings.clientId,
            secret: config.settings.secret,
        });

        logger.info('Executing the login callback');
        const authServiceInfo = await authClient.loginCallback(
            code,
            callbackUrl
        );
        logger.info('Executed the login callback');

        logger.info('Generating a JWT');
        const { token: externalToken } = await authClient.generateJwt(
            authServiceInfo.access_token
        );
        logger.info('Generated a JWT');

        return {
            externalToken,
        };
    },
    getAuthServiceInfo: async (req, res, next) => {
        logger.info('Getting external auth service configuration');
        const config = externalAuthService.getConfiguration(
            externalTokenVerifierConfig
        );
        logger.info('Got the external auth service configuration');

        return {
            adapter: config.settings.adapter,
            settings: config.frontendSettings,
        };
    },
};
