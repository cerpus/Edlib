import { getUserDataFromToken as _getUserDataFromToken } from './helpers.js';

const getEnvConfiguration = (options) => {
    if (!options) {
        throw new Error('Missing auth0 configuration');
    }

    const frontendSettings = {
        domain: options.domain,
        clientId: options.clientId,
        audience: options.audience,
    };

    if (
        !frontendSettings.domain ||
        !frontendSettings.clientId ||
        !frontendSettings.audience ||
        !options.propertyPaths.id ||
        !options.propertyPaths.email ||
        !options.propertyPaths.name
    ) {
        throw new Error('Missing auth0 configuration');
    }

    return {
        frontendSettings,
        settings: {
            ...frontendSettings,
            propertyPaths: options.propertyPaths,
        },
    };
};
export default () => ({
    getEnvConfiguration,
    getUserDataFromToken: _getUserDataFromToken,
    getDefaultPropertyPaths: async () => ({
        id: 'https://edlib.com/id',
        email: 'https://edlib.com/email',
        name: 'https://edlib.com/name',
        isAdminMethod: 'inscope',
        isAdminInScopeKey: 'scope',
        isAdminInScopeValue: 'edlib:superadmin',
    }),
});
