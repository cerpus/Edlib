import apiConfig from '../../config/apis.js';
import { apiClients, services } from '@cerpus/edlib-node-utils';

export default (req) => {
    const idApi = apiClients.id(req, apiConfig.id);
    const licenseApi = apiClients.license(req, apiConfig.license, {
        idApiClient: idApi,
    });
    const versionApi = apiClients.version(req, apiConfig.version);
    const coreInternalApi = apiClients.coreInternal(
        req,
        apiConfig.coreInternal
    );
    const statusService = services.status({
        licenseApi,
        versionApi,
        coreInternalApi,
    });

    return {
        license: licenseApi,
        version: versionApi,
        coreInternal: coreInternalApi,
        status: statusService,
    };
};
