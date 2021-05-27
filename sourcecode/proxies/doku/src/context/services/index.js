import coreExternal from '@cerpus/edlib-node-utils/apiClients/coreExternal/index.js';
import auth from '@cerpus/edlib-node-utils/apiClients/auth/index.js';
import embedly from './embedly.js';
import status from '@cerpus/edlib-node-utils/services/status.js';
import doku from '../../services/doku';
import apiConfig from '../../config/apis.js';

export default (req, res) => {
    const authApi = auth(req, apiConfig.auth);
    const coreExternalApi = coreExternal(req, apiConfig.core);
    const statusService = status({
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
