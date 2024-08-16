import ApiException, { errorResponse } from './apiException.js';

export default class NotFound extends ApiException {
    parameter;

    constructor(parameter) {
        super(`${parameter} was not found`);
        this.parameter = parameter;
        this.report = false;
    }

    getStatus() {
        return 404;
    }

    getBody() {
        return errorResponse(this.message, 'not_found', {
            parameter: this.parameter,
        });
    }
}
