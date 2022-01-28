import apiConfig from '../../config/apis.js';
import { apiClients, services } from '@cerpus/edlib-node-utils';

export default (req) => {
    let authApi;
    if (apiConfig.externalAuth.adapter === 'cerpusauth') {
        authApi = apiClients.auth(req, {
            url: apiConfig.externalAuth.adapterSettings.public.url,
            clientId: apiConfig.externalAuth.adapterSettings.public.clientId,
            secret: apiConfig.externalAuth.adapterSettings.private.secret,
        });
    }

    const statusService = services.status({
        authApi: authApi,
    });

    return {
        auth: authApi,
        edlibAuth: apiClients.edlibAuth(req, apiConfig.edlibAuth),
        status: statusService,
    };
};
