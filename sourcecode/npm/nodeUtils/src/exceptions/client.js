import ApiException, { errorResponse } from './apiException.js';

export default class Client extends ApiException {
    parameter;

    constructor() {
        super();
        this.report = false;
    }

    getStatus() {
        return 404;
    }

    getBody() {
        return errorResponse(this.message, 'client', {});
    }
}
