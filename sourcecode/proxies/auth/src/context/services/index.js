import apiConfig from '../../config/apis.js';
import { apiClients, services } from '@cerpus/edlib-node-utils';

export default (req) => {
    const authApi = apiClients.auth(req, apiConfig.externalAuth);
    const statusService = services.status({
        authApi: authApi,
    });

    return {
        auth: authApi,
        edlibAuth: apiClients.edlibAuth(req, apiConfig.edlibAuth),
        status: statusService,
    };
};
