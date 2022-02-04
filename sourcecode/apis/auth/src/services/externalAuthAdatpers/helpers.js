import _ from 'lodash';

export const getUserDataFromToken = (payload, propertyPaths) => {
    let firstName, lastName, isAdmin;

    if (propertyPaths.name) {
        const fullName = _.get(payload, propertyPaths.name);

        if (fullName.split(' ').length > 1) {
            const lastIndexOfSpace = fullName.lastIndexOf(' ');
            lastName = fullName.substring(lastIndexOfSpace + 1);
            firstName = fullName.substring(0, lastIndexOfSpace);
        } else {
            firstName = fullName;
            lastName = '';
        }
    } else {
        firstName = _.get(payload, propertyPaths.firstName);
        lastName = _.get(payload, propertyPaths.lastName);
    }

    if (!propertyPaths.isAdminMethod) {
        isAdmin = _.get(payload, propertyPaths.isAdmin);
    } else {
        isAdmin =
            _.get(payload, propertyPaths.isAdminInScopeKey)
                .split(' ')
                .indexOf(propertyPaths.isAdminInScopeValue) !== -1;
    }

    return {
        id: _.get(payload, propertyPaths.id),
        email: _.get(payload, propertyPaths.email),
        firstName,
        lastName,
        isAdmin,
    };
};
