import apiConfig from '../../config/apis.js';
import { apiClients, services } from '@cerpus/edlib-node-utils';

export default (req) => {
    const versionApi = apiClients.version(req, apiConfig.version);
    const coreInternalApi = apiClients.coreInternal(
        req,
        apiConfig.coreInternal
    );
    const statusService = services.status({
        versionApi,
        coreInternalApi,
    });

    return {
        version: versionApi,
        coreInternal: coreInternalApi,
        status: statusService,
    };
};
