import apiConfig from '../config/apis.js';
import { apiClients, services } from '@cerpus/edlib-node-utils';
import resource from './services/resource.js';

export const buildRawContext = (req) => {
    const idApi = apiClients.id(req, apiConfig.id);
    const coreExternalApi = apiClients.coreExternal(req, apiConfig.core);
    const coreInternalApi = apiClients.coreInternal(
        req,
        apiConfig.coreInternal
    );
    const authApi = apiClients.auth(req, apiConfig.auth);
    const licenseApi = apiClients.license(req, apiConfig.licenseApi, {
        idApiClient: idApi,
    });
    const versionApi = apiClients.version(req, apiConfig.version);

    const statusService = services.status({
        authApi: authApi,
        licenseApi,
        coreExternalApi,
        idApi,
    });

    return {
        services: {
            version: versionApi,
            id: idApi,
            auth: authApi,
            coreExternal: coreExternalApi,
            coreInternal: coreInternalApi,
            license: licenseApi,
            resource: resource(req),
            status: statusService,
            edlibAuth: apiClients.edlibAuth(req, apiConfig.edlibAuth),
        },
    };
};

const getContext = (req, res) => ({
    res,
    req,
    ...buildRawContext(req, res),
});

export default getContext;
