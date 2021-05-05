import { UnauthorizedException } from '../exceptions/index.js';
import isUserAuthenticated from './isUserAuthenticated.js';

export default (req, res, next) => {
    isUserAuthenticated(req, res, (err) => {
        if (err) {
            return next(err);
        }

        if (!req.user.admin) {
            throw new UnauthorizedException();
        }

        next();
    });
};
