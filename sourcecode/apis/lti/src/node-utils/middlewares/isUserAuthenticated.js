import { UnauthorizedException } from '../exceptions/index.js';
import addUserToRequest from './addUserToRequest.js';

export default (req, res, next) => {
    if (req.user === undefined) {
        return addUserToRequest(req, res, (err) => {
            if (err) {
                return next(err);
            }

            next(!req.user && new UnauthorizedException());
        });
    }

    return next(!req.user && new UnauthorizedException());
};
