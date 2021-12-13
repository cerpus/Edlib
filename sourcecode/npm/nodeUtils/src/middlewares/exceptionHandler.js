import { ApiException } from '../exceptions/index.js';
import appConfig from '../envConfig/app.js';
import logger from '../services/logger.js';
import { getReasonPhrase } from 'http-status-codes';

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
        let report = true;

        if (err instanceof ApiException) {
            status = err.getStatus();
            body = err.getBody();
            report = err.report;
        }

        if (appConfig.displayDetailedErrors) {
            body.trace = err.stack;
        } else {
            body.message = null;
        }

        if (report) {
            if (err.logDetails) {
                err.logDetails();
            } else {
                logger.error(err.stack);
            }
        }

        res.status(status);

        if (req.accepts('html')) {
            try {
                return res.render('errorPage', {
                    message: body.message,
                    status,
                    statusPhrase: getReasonPhrase(status),
                    stack: body.trace,
                });
            } catch (e) {}
        }

        res.json({
            ...body,
            message: body.message || getReasonPhrase(status),
        });
    };

    then();
};
