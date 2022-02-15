import _ from 'lodash';
import {
    ValidationException,
    validationExceptionError,
} from '@cerpus/edlib-node-utils';

export const getUserDataFromToken = (payload, propertyPaths) => {
    const data = {
        id: _.get(payload, propertyPaths.id),
        email: _.get(payload, propertyPaths.email),
    };

    if (!data.id) {
        throw new ValidationException(
            validationExceptionError(
                'externalToken',
                'body',
                'Id could not be extracted from the payload'
            )
        );
    }

    if (propertyPaths.name) {
        const fullName = _.get(payload, propertyPaths.name);

        if (fullName.split(' ').length > 1) {
            const lastIndexOfSpace = fullName.lastIndexOf(' ');
            data.lastName = fullName.substring(lastIndexOfSpace + 1);
            data.firstName = fullName.substring(0, lastIndexOfSpace);
        } else {
            data.firstName = fullName;
            data.lastName = '';
        }
    } else {
        data.firstName = _.get(payload, propertyPaths.firstName);
        data.lastName = _.get(payload, propertyPaths.lastName);
    }

    if (!propertyPaths.isAdminMethod) {
        data.isAdmin = _.get(payload, propertyPaths.isAdmin);
    } else {
        data.isAdmin =
            _.get(payload, propertyPaths.isAdminInScopeKey)
                .split(' ')
                .indexOf(propertyPaths.isAdminInScopeValue) !== -1;
    }

    return data;
};
