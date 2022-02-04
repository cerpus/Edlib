import { getUserDataFromToken as _getUserDataFromToken } from './helpers.js';
import { ApiException } from '@cerpus/edlib-node-utils';

const getConfiguration = (options) => {
    if (!options) {
        throw new Error('Missing Cerpus Auth configuration');
    }

    const frontendSettings = {
        url: options.url,
        clientId: options.clientId,
    };

    const privateSettings = {
        secret: options.secret,
    };

    if (
        !frontendSettings.url ||
        !frontendSettings.clientId ||
        !privateSettings.secret
    ) {
        throw new Error('Missing Cerpus Auth configuration');
    }

    return {
        frontendSettings,
        settings: {
            ...frontendSettings,
            ...privateSettings,
        },
    };
};

export default () => ({
    getConfiguration,
    getUserDataFromToken: (payload, propertyPaths) =>
        _getUserDataFromToken(payload, {
            id: 'app_metadata.identityId',
            email: 'app_metadata.email',
            firstName: 'app_metadata.firstName',
            lastName: 'app_metadata.lastName',
            isAdmin: 'app_metadata.admin',
        }),
    getPropertyPathsFromDb: async () => ({}),
});
