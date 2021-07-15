import { middlewares } from '@cerpus/edlib-node-utils';
import context from '../context/index.js';

export default (req, res, next) => {
    req.context = context(req, res);

    middlewares.addUserToRequest(req, res, (err) => {
        if (err) {
            return next(err);
        }
        req.context.user = req.user;
        next();
    });
};
