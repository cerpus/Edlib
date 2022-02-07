import logger from '../services/logger.js';
import authorizationJwtMiddleware from './authorizationJwt.js';
import { ApiException } from '../exceptions/index.js';
import * as errorReporting from '../services/errorReporting.js';
import moment from 'moment';

export default (req, res, next) => {
    if (req.user) {
        next();
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
                const tokenPayload =
                    await req.context.services.edlibAuth.verifyTokenAgainstAuth(
                        req.authorizationJwt,
                        {
                            ignoreExpiration: true,
                        }
                    );

                if (tokenPayload) {
                    info = {
                        exp: tokenPayload.exp,
                        user: {
                            ...tokenPayload.payload.user,
                            identityId: tokenPayload.payload.user.id,
                        },
                    };
                }
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
