import externalAuthService from '../services/externalAuth.js';
import jwksProviderService from '../services/jwksProvider.js';
import externalTokenVerifierConfig from '../config/externalTokenVerifier.js';

import _ from 'lodash';
import { UnauthorizedException } from '@cerpus/edlib-node-utils/exceptions/index.js';
import { pubsub } from '@cerpus/edlib-node-utils/services/index.js';

export default {
    convertToken: async (req) => {
        const payload = await externalAuthService.verifyToken(
            req.body.externalToken
        );

        const user = Object.entries(
            externalTokenVerifierConfig.propertyPaths
        ).reduce((user, [property, path]) => {
            return { ...user, [property]: _.get(payload, path) };
        }, {});

        user.isAdmin = user.isAdmin ? 1 : 0;

        let dbUser = await req.context.db.user.getById(user.id);
        if (!dbUser) {
            dbUser = await req.context.db.user.create(user);

            await pubsub.publish(
                req.context.pubSubConnection,
                'edlib_new_user',
                JSON.stringify({
                    user: dbUser,
                })
            );
        } else {
            dbUser = await req.context.db.user.update(user.id, {
                ...user,
                lastSeen: new Date(),
                updatedAt: Object.keys(
                    externalTokenVerifierConfig.propertyPaths
                ).some((key) => dbUser[key] !== user[key])
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
};
