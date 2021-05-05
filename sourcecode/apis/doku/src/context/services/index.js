import apiConfig from '../../config/apis.js';
import license from '@cerpus-private/edlib-node-utils/apiClients/license/index.js';
import version from '@cerpus-private/edlib-node-utils/apiClients/version/index.js';
import status from '@cerpus-private/edlib-node-utils/services/status.js';
import id from '@cerpus-private/edlib-node-utils/apiClients/id/index.js';
import coreInternal from '@cerpus-private/edlib-node-utils/apiClients/coreInternal/index.js';

export default (req) => {
    const idApi = id(req, apiConfig.id);
    const licenseApi = license(req, apiConfig.license, {
        idApiClient: idApi,
    });
    const versionApi = version(req, apiConfig.version);
    const coreInternalApi = coreInternal(req, apiConfig.coreInternal);
    const statusService = status({
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
