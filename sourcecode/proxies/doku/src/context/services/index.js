import { apiClients, services } from '@cerpus/edlib-node-utils';
import embedly from './embedly.js';
import doku from '../../services/doku/index.js';
import apiConfig from '../../config/apis.js';

export default (req, res) => {
    const authApi = apiClients.auth(req, apiConfig.auth);
    const coreExternalApi = apiClients.coreExternal(req, apiConfig.core);
    const statusService = services.status({
        coreExternalApi,
        authApi,
    });

    return {
        auth: authApi,
        coreExternal: coreExternalApi,
        doku: doku(req, res),
        status: statusService,
        embedly: embedly(),
    };
};
