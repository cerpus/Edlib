import logger from '../services/logger.js';
import authorizationJwtMiddleware from './authorizationJwt.js';
import { ApiException } from '../exceptions/index.js';
import * as errorReporting from '../services/errorReporting.js';
import moment from 'moment';
import JsonWebToken from 'jsonwebtoken';

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

        req.context.services.auth
            .verifyTokenAgainstAuth(req.authorizationJwt, {
                ignoreExpiration: true,
            })
            .then((r) => {
                const leeway = 60 * 60;
                const exp = moment.unix(r.exp + leeway);

                if (!exp.isAfter(moment())) {
                    throw new JsonWebToken.TokenExpiredError(
                        'jwt expired',
                        new Date(exp.unix() * 1000)
                    );
                }

                req.authTokenContent = r;
                req.user = r.app_metadata;

                errorReporting.setUser({
                    id: req.user.identityId,
                });

                next();
            })
            .catch((e) => {
                logger.error(e);
                next();
            });
    });
};
