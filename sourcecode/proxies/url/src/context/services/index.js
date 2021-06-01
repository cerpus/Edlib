import apiConfig from '../../config/apis.js';
import auth from '@cerpus/edlib-node-utils/apiClients/auth/index.js';
import edlibAuth from '@cerpus/edlib-node-utils/apiClients/edlibAuth/index.js';
import url from './url.js';

export default (req, res) => {
    const authApi = auth(req, apiConfig.auth);

    return {
        edlibAuth: edlibAuth(req, apiConfig.edlibAuth),
        auth: authApi, // @todo remove when authentication is working
        url: url(),
    };
};
