import logger from './logger.js';
import { ApiException } from '../exceptions';

export const setUser = ({ id }) => {};

export const init = (expressApp, { url } = {}) => {};

export const setupTrace = (req, res) => {
    req.__trace = null;
};

export const getTraceHeaders = (req) => {
    return {};
};

export const logExpressError = (error, req, res, next) => {
    next(error);
};

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

    logger.error(e);
    logger.error('Extra map', extraMap);
};
