import apiConfig from '../config/apis.js';
import { apiClients, services } from '@cerpus/edlib-node-utils';
import resource from './services/resource.js';

export const buildRawContext = (req) => {
    const idApi = apiClients.id(req, apiConfig.id);
    const versionApi = apiClients.version(req, apiConfig.version);

    const statusService = services.status({
        idApi,
    });

    return {
        services: {
            version: versionApi,
            id: idApi,
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
