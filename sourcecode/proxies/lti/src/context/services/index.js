import { apiClients, services } from '@cerpus/edlib-node-utils';
import apiConfig from '../../config/apis.js';
import resource from './resource.js';
import lti from './lti.js';

export default (req) => {
    const authApi = apiClients.auth(req, apiConfig.auth);
    const coreExternalApi = apiClients.coreExternal(req, apiConfig.core);
    const statusService = services.status({
        coreExternalApi,
        authApi,
    });
    const resourceService = resource();
    const ltiService = lti();

    return {
        auth: authApi,
        coreExternal: coreExternalApi,
        status: statusService,
        resource: resourceService,
        lti: ltiService,
        edlibAuth: apiClients.edlibAuth(req, apiConfig.edlibAuth),
    };
};
