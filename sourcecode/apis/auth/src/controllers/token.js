import Joi from 'joi';
import {
    UnauthorizedException,
    pubsub,
    NotFoundException,
    validateJoi,
    ValidationException,
    validationExceptionError,
} from '@cerpus/edlib-node-utils';
import JsonWebToken from 'jsonwebtoken';

import appConfig from '../config/app.js';
import externalTokenVerifierConfig from '../config/externalTokenVerifier.js';
import externalAuthService from '../services/externalAuth.js';
import jwksProviderService from '../services/jwksProvider.js';

export default {
    convertToken: async (req) => {
        const { externalToken } = validateJoi(
            req.body,
            Joi.object({
                externalToken: Joi.string().min(1).required(),
            })
        );

        let user;
        let roles = [];
        const { data, iss } = await JsonWebToken.decode(externalToken);

        if (iss === 'fake') {
            // We have a fake issuer in order to test easier locally
            if (!appConfig.allowFakeToken) {
                throw new ValidationException(
                    validationExceptionError(
                        'externalToken',
                        'body',
                        'Token cannot be fake'
                    )
                );
            }
            if (!data || !data.isFakeToken || !data.user) {
                throw new ValidationException(
                    validationExceptionError(
                        'externalToken',
                        'body',
                        "Token doesn't have the correct fake format"
                    )
                );
            }

            user = {
                id: data.user.id,
                firstName: data.user.firstName,
                lastName: data.user.lastName,
                email: data.user.email,
                isAdmin: data.user.isAdmin,
            };
        } else if (iss === externalTokenVerifierConfig.issuer) {
            // If the issuer is the one set in the environment variables we use the configuration from there
            const payload = await externalAuthService.verifyToken(
                externalTokenVerifierConfig.wellKnownEndpoint,
                externalToken
            );

            user = externalAuthService.getUserDataFromToken(
                externalTokenVerifierConfig.adapter,
                payload,
                await externalAuthService.getPropertyPaths(
                    req.context,
                    externalTokenVerifierConfig.adapter,
                    externalTokenVerifierConfig[
                        externalTokenVerifierConfig.adapter
                    ].propertyPaths
                )
            );
        } else {
            const tenantAuthMethod =
                await req.context.db.tenantAuthMethod.getByIssuer(iss);

            if (!tenantAuthMethod) {
                throw new ValidationException(
                    validationExceptionError(
                        'externalToken',
                        'body',
                        'Token issuer was not found'
                    )
                );
            }

            const payload = await externalAuthService.verifyToken(
                tenantAuthMethod.jwksEndpoint,
                externalToken
            );

            user = externalAuthService.getUserDataFromToken(
                tenantAuthMethod.adapter,
                payload,
                await externalAuthService.getPropertyPathsFromTenantAuthMethod(
                    req.context,
                    tenantAuthMethod
                )
            );
        }

        if (user.isAdmin) {
            roles.push('superadmin');
        }

        user.isAdmin = user.isAdmin ? 1 : 0;

        let dbUser = await req.context.db.user.getById(user.id);
        let shouldUpdate = !!dbUser;

        if (!shouldUpdate) {
            try {
                dbUser = await req.context.db.user.create(user);

                await pubsub.publish(
                    req.context.pubSubConnection,
                    'edlib_new_user',
                    JSON.stringify({
                        user: dbUser,
                    })
                );
            } catch (e) {
                if (e.code === 'ER_DUP_ENTRY') {
                    shouldUpdate = true;
                    dbUser = await req.context.db.user.getById(user.id);

                    if (!dbUser) {
                        throw new NotFoundException('user');
                    }
                } else {
                    throw e;
                }
            }
        }

        if (shouldUpdate) {
            dbUser = await req.context.db.user.update(user.id, {
                ...user,
                lastSeen: new Date(),
                updatedAt: Object.keys(user).some(
                    (key) => dbUser[key] !== user[key]
                )
                    ? new Date()
                    : undefined,
            });
        }

        const { token, expiresAt } = await jwksProviderService.encrypt(
            req.context,
            { type: 'user', user, roles },
            1,
            user.id
        );

        return {
            user,
            roles,
            token,
            expiresAt,
        };
    },
    refresh: async (req) => {
        const { type, user, roles } = await jwksProviderService.verify(
            req.context,
            req.body.token
        );

        if (type !== 'user') {
            throw new UnauthorizedException();
        }

        const { token, expiresAt } = await jwksProviderService.encrypt(
            req.context,
            { type: 'user', user, roles },
            1,
            user.id
        );

        return {
            user,
            token,
            expiresAt,
        };
    },
    createForLtiUser: async (req) => {
        const validatedData = validateJoi(
            req.body,
            Joi.object({
                registrationId: Joi.string().min(1).required(),
                deploymentId: Joi.string().min(1).required(),
                externalId: Joi.string().min(1).required(),
                email: Joi.string()
                    .min(1)
                    .allow(null)
                    .empty(null)
                    .optional()
                    .default(null),
                firstName: Joi.string()
                    .min(1)
                    .allow(null)
                    .empty(null)
                    .optional()
                    .default(null),
                lastName: Joi.string()
                    .min(1)
                    .allow(null)
                    .empty(null)
                    .optional()
                    .default(null),
            })
        );

        const existing = await req.context.db.ltiUser.getByLtiReference(
            validatedData.registrationId,
            validatedData.deploymentId,
            validatedData.externalId
        );

        let dbLtiUser;
        if (!existing) {
            dbLtiUser = await req.context.db.ltiUser.create(validatedData);
        } else {
            dbLtiUser = await req.context.db.ltiUser.update(existing.id, {
                email: validatedData.email,
                firstName: validatedData.firstName,
                lastName: validatedData.lastName,
            });
        }

        const modifiedUser = {
            ...dbLtiUser,
            id: appConfig.ltiUserPrefix + dbLtiUser.id,
            isAdmin: 0,
        };

        return {
            user: modifiedUser,
            token: (
                await jwksProviderService.encrypt(
                    req.context,
                    {
                        type: 'user',
                        userType: 'lti',
                        user: modifiedUser,
                    },
                    1,
                    modifiedUser.id
                )
            ).token,
        };
    },
};
