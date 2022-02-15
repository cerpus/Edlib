import apiConfig from '../config/apis.js';
import { apiClients, services } from '@cerpus/edlib-node-utils';
import resource from './services/resource.js';

export const buildRawContext = (req) => {
    const idApi = apiClients.id(req, apiConfig.id);
    const licenseApi = apiClients.license(req, apiConfig.licenseApi, {
        idApiClient: idApi,
    });
    const versionApi = apiClients.version(req, apiConfig.version);

    const statusService = services.status({
        licenseApi,
        idApi,
    });

    return {
        services: {
            version: versionApi,
            id: idApi,
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
