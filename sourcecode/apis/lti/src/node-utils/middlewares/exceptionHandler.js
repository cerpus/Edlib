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
                const msg = body.message.replace('&', '&amp;').replace('<', '&lt;').replace('>', '&gt;');
                const stack = (body.trace || '').replace('&', '&amp;').replace('<', '&lt;').replace('>', '&gt;');

                return res.send(`<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title>${msg}</title>
</head>
<body>
    <h1>${status}: ${msg}</h1>
    <pre>${stack}</pre>
</body>
</html>`);
            } catch (e) {}
        }

        res.json({
            ...body,
            message: body.message || getReasonPhrase(status),
        });
    };

    then();
};
