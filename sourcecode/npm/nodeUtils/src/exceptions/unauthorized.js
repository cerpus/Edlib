import ApiException, { errorResponse } from './apiException.js';

export default class Unauthorized extends ApiException {
    constructor(message = 'You do not have access to this resource') {
        super(message);
        this.report = false;
    }

    getStatus() {
        return 401;
    }

    getBody() {
        return errorResponse(this.message, 'unauthorized');
    }
}
