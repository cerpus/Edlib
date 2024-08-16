import ApiException, { errorResponse } from './apiException.js';

export default class Validation extends ApiException {
    errors;

    constructor(errors = []) {
        super('The request is not valid');
        this.report = false;

        if (Array.isArray(errors)) {
            this.errors = errors;
        } else {
            this.errors = [errors];
        }
    }

    getStatus() {
        return 422;
    }

    getBody() {
        return errorResponse(this.message, 'validation', {
            messages: this.errors,
        });
    }
}

export const validationError = (key, location, message) => ({
    key,
    location,
    message,
});
