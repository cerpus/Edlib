import {
    UnauthorizedException,
    NotFoundException,
    ApiException,
    ValidationException,
    ClientException,
} from '../exceptions/index.js';
import logger from './logger.js';
import AxiosException from '../exceptions/axiosException.js';
import EndpointNotFoundException from '../exceptions/endpointNotFound.js';

export default (e, serviceName = 'API') => {
    if (!e.response) {
        throw e;
    }

    if ([302, 401].indexOf(e.response.status) !== -1) {
        throw new UnauthorizedException();
    }

    const responseData = e.response.data;

    if (e.response.status === 400) {
        throw new ClientException();
    }

    if (e.response.status === 404) {
        if (
            responseData &&
            responseData.type === 'endpoint_not_found' &&
            responseData.error
        ) {
            throw new EndpointNotFoundException(
                e.config.url,
                e.config.method,
                true
            );
        }

        let field =
            responseData.error && responseData.error.parameter
                ? responseData.error.parameter
                : 'unknown';

        throw new NotFoundException(field);
    }

    if (e.response.status === 422) {
        let errors = [];
        if (
            e.response.data.type === 'validation' &&
            Array.isArray(e.response.data.error.messages)
        ) {
            errors = e.response.data.error.messages;
        }
        throw new ValidationException(errors);
    }

    logger.error(
        `Request to ${serviceName} failed with an unexpected error. Status code is: ${e.response.status}`
    );
    logger.error(e.response.status);
    logger.error(e.response.data);

    if (e.isAxiosError) {
        throw new AxiosException(e);
    }

    throw new ApiException('Service request failed');
};
