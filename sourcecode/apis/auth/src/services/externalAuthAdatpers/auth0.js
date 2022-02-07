import { getUserDataFromToken as _getUserDataFromToken } from './helpers.js';
import { ApiException } from '@cerpus/edlib-node-utils';

const getConfiguration = (options) => {
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
    getConfiguration,
    getUserDataFromToken: (payload, propertyPaths) =>
        _getUserDataFromToken(payload, {
            id: propertyPaths.id,
            email: propertyPaths.email,
            name: propertyPaths.name,
            isAdminMethod: 'inscope',
            isAdminInScopeKey: 'scope',
            isAdminInScopeValue: 'edlib:superadmin',
        }),
    getPropertyPathsFromDb: async (context, tenantAuthMethodId) => {
        const tenantAuthMethodAuthZero =
            await context.db.tenantAuthMethodAuthZero.getByTenantAuthMethodId(
                tenantAuthMethodId
            );

        if (!tenantAuthMethodAuthZero) {
            throw new ApiException(
                'Missing Auth0 configuration for tenant auth method with id ' +
                    tenantAuthMethodId
            );
        }

        return {
            id: tenantAuthMethodAuthZero.propertyPathId,
            email: tenantAuthMethodAuthZero.propertyPathEmail,
            name: tenantAuthMethodAuthZero.propertyPathName,
        };
    },
});
