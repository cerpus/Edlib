import logger from '../services/logger.js';

export default class ApiException extends Error {
    status = 500;
    report = true;

    constructor(message, status = 500, report = true) {
        super(message);
        this.status = status;
        this.report = report;
    }

    getStatus() {
        return this.status;
    }

    getBody() {
        return errorResponse(this.message, 'server_error');
    }

    logDetails() {
        logger.error(this.message);
        logger.error(this.stack);
    }

    getExtraMap() {
        return {};
    }
}

export const errorResponse = (message, type, error = null) => ({
    type,
    message,
    error,
});
