import logger from '../services/logger.js';
import authorizationJwtMiddleware from './authorizationJwt.js';
import { ApiException } from '../exceptions/index.js';
import * as errorReporting from '../services/errorReporting.js';
import moment from 'moment';

export default (req, res, next) => {
    if (req.user) {
        next();
    }

    if (!req.context || !req.context.services || !req.context.services.auth) {
        throw new ApiException(
            'In order to authenticate users the variable req.context.services.auth must be set with the auth api client'
        );
    }

    req.user = null;

    authorizationJwtMiddleware(req, res, (err) => {
        if (err) {
            return next(err);
        }

        if (!req.authorizationJwt) {
            return next();
        }

        const verify = async () => {
            let info = null;
            if (req.context.services.edlibAuth) {
                try {
                    const r =
                        await req.context.services.edlibAuth.verifyTokenAgainstAuth(
                            req.authorizationJwt,
                            {
                                ignoreExpiration: true,
                            }
                        );

                    info = {
                        exp: r.exp,
                        user: {
                            ...r.payload.user,
                            identityId: r.payload.user.id,
                        },
                    };
                } catch (e) {
                    logger.error(e);
                }
            }

            if (!info) {
                const r =
                    await req.context.services.auth.verifyTokenAgainstAuth(
                        req.authorizationJwt,
                        {
                            ignoreExpiration: true,
                        }
                    );

                info = {
                    exp: r.exp,
                    user: {
                        ...r.app_metadata,
                        id: r.app_metadata.identityId,
                        isAdmin: r.app_metadata.admin,
                    },
                };
            }

            if (!info) {
                return null;
            }

            const leeway = 60 * 60;
            const exp = moment.unix(info.exp + leeway);

            if (!exp.isAfter(moment())) {
                return null;
            }

            req.user = info.user;

            errorReporting.setUser({
                id: req.user.id,
            });
        };

        verify()
            .then(() => next())
            .catch((e) => {
                logger.error(e);
                next();
            });
    });
};
