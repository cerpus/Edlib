import ApiException, { errorResponse } from './apiException.js';

export default class EndpointNotFound extends ApiException {
    path;
    method;

    constructor(path, method) {
        super('Endpoint was not found');
        this.path = path;
        this.method = method;
        this.report = false;
    }

    getStatus() {
        return 404;
    }

    getBody() {
        return errorResponse(this.message, 'endpoint_not_found', {
            path: this.path,
            method: this.method,
        });
    }
}
