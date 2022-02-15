import { getUserDataFromToken as _getUserDataFromToken } from './helpers.js';

const getConfiguration = (options) => {
    if (!options) {
        throw new Error('Missing generic configuration');
    }

    if (
        !options.propertyPaths.id ||
        !options.propertyPaths.email ||
        !options.propertyPaths.name
    ) {
        throw new Error('Missing generic configuration');
    }

    return {
        frontendSettings: {},
        settings: {
            propertyPaths: options.propertyPaths,
        },
    };
};

export default () => ({
    getConfiguration,
    getUserDataFromToken: _getUserDataFromToken,
    getDefaultPropertyPaths: async () => ({
        id: 'https://edlib.com/id',
        email: 'https://edlib.com/email',
        firstName: 'https://edlib.com/firstName',
        lastName: 'https://edlib.com/lastName',
        isAdminMethod: 'inscope',
        isAdminInScopeKey: 'scope',
        isAdminInScopeValue: 'edlib:superadmin',
    }),
});
