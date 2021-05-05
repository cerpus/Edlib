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
}

export const errorResponse = (message, type, error = null) => ({
    type,
    message,
    error,
});
