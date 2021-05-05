import _ from 'lodash';
import {
    ValidationException,
    validationExceptionError,
} from '../exceptions/index.js';

export default (data, schema) => {
    const result = schema.validate(data, {
        abortEarly: false,
        stripUnknown: true,
    });

    if (result && result.error && result.error.details) {
        throw new ValidationException(
            result.error.details.map((detail) => {
                const key = detail.path.join('.');
                const filterFromMessage = `"${key}" `;

                return validationExceptionError(
                    key,
                    'body',
                    _.upperFirst(
                        detail.message.startsWith(filterFromMessage)
                            ? detail.message.substring(filterFromMessage.length)
                            : detail.message
                    )
                );
            })
        );
    }

    return result.value;
};
