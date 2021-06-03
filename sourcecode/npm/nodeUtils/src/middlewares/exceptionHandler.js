import { ApiException } from '../exceptions/index.js';
import appConfig from '../envConfig/app.js';
import logger from '../services/logger.js';

export default (
    err,
    req,
    res,
    next // eslint-disable-line
) => {
    const then = () => {
        let body = {
            success: false,
            message: 'Server error',
            extra: err,
        };

        let status = 500;

        if (err instanceof ApiException) {
            status = err.getStatus();
            body = err.getBody();
        }

        if (!appConfig.isProduction) {
            body.trace = err.stack;
        }

        if (err.logDetails) {
            err.logDetails();
        } else {
            logger.error(err.stack);
        }

        res.status(status).json(body);
    };

    then();
};
