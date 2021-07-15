import { apiClients, services } from '@cerpus/edlib-node-utils';
import recommender from '../services/recommender/index.js';
import apiConfig from '../config/apis.js';
import resource from './servcies/resource.js';

export const buildRawContext = (req, res) => {
    const authApi = apiClients.auth(req, apiConfig.auth);
    const coreExternalApi = apiClients.coreExternal(req, apiConfig.core);
    const coreInternalApi = apiClients.coreInternal(
        req,
        apiConfig.coreInternal
    );
    const statusService = services.status({
        authApi: authApi,
        coreExternalApi,
    });

    return {
        services: {
            coreExternal: coreExternalApi,
            coreInternal: coreInternalApi,
            recommender: recommender(req),
            resource: resource(req),
            auth: authApi,
            status: statusService,
            edlibAuth: apiClients.edlibAuth(req, apiConfig.edlibAuth),
        },
    };
};

const getContext = (req, res) => ({
    user: req.user,
    res,
    req,
    ...buildRawContext(req, res),
});

export default getContext;
