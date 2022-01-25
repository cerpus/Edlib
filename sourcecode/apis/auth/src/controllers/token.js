import externalAuthService from '../services/externalAuth.js';
import jwksProviderService from '../services/jwksProvider.js';
import externalTokenVerifierConfig from '../config/externalTokenVerifier.js';
import Joi from 'joi';
import _ from 'lodash';
import {
    UnauthorizedException,
    pubsub,
    NotFoundException,
    validateJoi,
} from '@cerpus/edlib-node-utils';
import appConfig from '../config/app.js';
import JsonWebToken from 'jsonwebtoken';

export default {
    convertToken: async (req) => {
        const { externalToken } = validateJoi(
            req.body,
            Joi.object({
                externalToken: Joi.string().min(1).required(),
            })
        );

        let user;

        if (appConfig.allowFakeToken) {
            const { data } = await JsonWebToken.decode(externalToken);
            if (data && data.user && data.isFakeToken) {
                user = {
                    id: data.user.id,
                    firstName: data.user.firstName,
                    lastName: data.user.lastName,
                    email: data.user.email,
                    isAdmin: data.user.isAdmin,
                };
            }
        }

        if (!user) {
            const payload = await externalAuthService.verifyToken(
                externalToken
            );

            user = externalAuthService.getUserDataFromToken(payload);
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

        return {
            user,
            token: await jwksProviderService.encrypt(
                req.context,
                { type: 'user', user },
                1,
                user.id
            ),
        };
    },
    refresh: async (req) => {
        const { type, user } = await jwksProviderService.verify(
            req.context,
            req.body.token
        );

        if (type !== 'user') {
            throw new UnauthorizedException();
        }

        return {
            user,
            token: await jwksProviderService.encrypt(
                req.context,
                { type: 'user', user },
                1,
                user.id
            ),
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
            token: await jwksProviderService.encrypt(
                req.context,
                {
                    type: 'user',
                    userType: 'lti',
                    user: modifiedUser,
                },
                1,
                modifiedUser.id
            ),
        };
    },
};
