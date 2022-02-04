import apiConfig from '../../config/apis.js';
import { apiClients } from '@cerpus/edlib-node-utils';

export default (req) => {
    return {
        edlibAuth: apiClients.edlibAuth(req, apiConfig.edlibAuth),
    };
};
