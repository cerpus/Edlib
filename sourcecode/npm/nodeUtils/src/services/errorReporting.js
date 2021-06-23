import * as Sentry from '@sentry/node';
import * as Tracing from '@sentry/tracing';
import { URL } from 'url';
import logger from './logger.js';
import appConfig from '../envConfig/app.js';
import { ApiException } from '../exceptions';

export const setUser = ({ id }) => {
    Sentry.setUser({
        id,
    });
};

export const init = (expressApp, { url } = {}) => {
    Sentry.init({
        dsn: url,
        integrations: [
            new Sentry.Integrations.Http({ tracing: true, breadcrumbs: true }),
            new Tracing.Integrations.Express({ app: expressApp }),
        ],
        environment: appConfig.environment,
        tracesSampler: (data) => {
            const url = new URL(data.request.url);
            if (url.pathname.endsWith('/_ah/health')) {
                return 0;
            }

            if (data.parentSampled) {
                return 1;
            }

            return 0.1;
        },
    });

    expressApp.use(
        Sentry.Handlers.requestHandler({
            user: false,
        })
    );
    expressApp.use(Sentry.Handlers.tracingHandler());
};

export const setupTrace = (req, res) => {
    req.__trace = null;

    if (res.__sentry_transaction) {
        const t = res.__sentry_transaction;
        req.__trace = `${t.traceId}-${t.spanId}-${t.sampled ? 1 : 0}`;
    }
};

export const getTraceHeaders = (req) => {
    if (req.__trace) {
        return {
            'sentry-trace': req.__trace,
        };
    }

    return {};
};

export const logExpressError = Sentry.Handlers.errorHandler({
    shouldHandleError: (error) => {
        if (error instanceof ApiException) {
            console.log(`should report ${error.report}`);
            return error.report;
        }

        return true;
    },
});

export const captureException = (e) => {
    let extraMap = {};

    if (e instanceof ApiException) {
        extraMap = e.getExtraMap();
    }

    if (e.isAxiosError) {
        extraMap.errorSource = 'axios';
        extraMap.responseData = e.response.data;
        extraMap.responseCode = e.response.status;
        extraMap.url = `${e.request.host}${e.request.path}`;
    }

    Sentry.captureException(e, {
        extra: extraMap,
    });

    logger.error(e);
    logger.error('Extra map', extraMap);
};
