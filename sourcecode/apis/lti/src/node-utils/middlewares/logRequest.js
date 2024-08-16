import OnFinished from 'on-finished';
import url from 'url';
import appConfig from '../envConfig/app.js';
import logger from '../services/logger.js';

const ignoreAgents = [
    'kube-probe/',
    'GoogleStackdriverMonitoring-UptimeChecks',
];

const getContentLength = (resOrReq) => {
    let size = parseInt(resOrReq.get('content-length'));

    return size ? size : 0;
};

export default () => (req, res, next) => {
    if (!appConfig.logRequests) {
        return next();
    }

    if (ignoreAgents.some(ua => ua.startsWith(req.get('User-Agent')))) {
        return next();
    }

    const start = Date.now();
    let requestInfo = {
        path: req.path,
        url: url.parse(req.url).pathname,
        query: req.query,
        method: req.method,
        params: req.params,
        body: req.body,
        remoteIp: req.connection.remoteAddress,
        userAgent: req.get('User-Agent'),
        requestSize: getContentLength(req),
    };

    const responseInfo = {
        status: res.statusCode,
    };

    OnFinished(res, () => {
        const requestTime = Date.now() - start;

        logger.info(`${requestInfo.method} ${requestInfo.path} - ${requestTime}ms`, {
            requestInfo,
            responseInfo,
        });
    });

    next();
};
