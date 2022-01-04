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
    const start = Date.now();
    let info = {
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

    if (
        !info.userAgent ||
        ignoreAgents.some((agent) => info.userAgent.startsWith(agent))
    ) {
        return next();
    }

    if (appConfig.logRequests) {
        OnFinished(res, () => {
            const requestTime = Date.now() - start;

            logger.debug(`${info.method} ${info.path} - ${requestTime}ms`, {
                httpRequest: {
                    method: info.method,
                    url: info.url,
                    userAgent: info.userAgent,
                    requestSize: info.requestSize,
                    requestTime,
                },
            });
        });
    }

    next();
};
