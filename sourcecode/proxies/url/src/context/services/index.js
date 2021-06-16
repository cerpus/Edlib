import apiConfig from '../../config/apis.js';
import { apiClients } from '@cerpus/edlib-node-utils';
import url from './url.js';

export default (req, res) => {
    const authApi = apiClients.auth(req, apiConfig.auth);

    return {
        edlibAuth: apiClients.edlibAuth(req, apiConfig.edlibAuth),
        auth: authApi, // @todo remove when authentication is working
        url: url(),
    };
};
