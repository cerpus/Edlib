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
            logger.error(err.stack);
            body.trace = err.stack;
        }

        res.status(status).json(body);
    };

    then();
};
