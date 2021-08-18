import ApiException, { errorResponse } from './apiException.js';
import logger from '../services/logger.js';

export default class EndpointNotFound extends ApiException {
    path;
    method;

    constructor(path, method, report = false) {
        super(`Endpoint ${method} ${path} was not found`);
        this.path = path;
        this.method = method;
        this.report = report;
    }

    getStatus() {
        return 404;
    }

    logDetails() {
        logger.error(this.message);
        logger.error(this.stack);
    }

    getBody() {
        return errorResponse(this.message, 'endpoint_not_found', {
            path: this.path,
            method: this.method,
        });
    }
}
