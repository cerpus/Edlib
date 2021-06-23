import ApiException, { errorResponse } from './apiException.js';
import logger from '../services/logger.js';

export default class AxiosException extends ApiException {
    requestInfo;
    response;

    constructor(exceptionFromAxios) {
        super('Request to external API failed');
        this.report = false;
        this.requestInfo = {
            url: exceptionFromAxios.config.url,
            method: exceptionFromAxios.config.method,
        };
        this.response = {
            status: exceptionFromAxios.response.status,
            data: exceptionFromAxios.response.data,
        };
    }

    getStatus() {
        return 500;
    }

    getBody() {
        return errorResponse(this.message, 'server_error', {
            externalAPIResponseStatus: this.response.status,
        });
    }

    getAxiosResponseStatus() {
        return this.response.status;
    }

    logDetails() {
        logger.error(this.message);
        logger.error(this.stack);
        logger.error('requestInfo', this.requestInfo);
        logger.error('response', this.response);
    }

    getExtraMap() {
        return {
            errorSource: 'axios',
            responseData: this.response.data,
            responseCode: this.response.status,
            url: this.requestInfo.url,
        };
    }
}
