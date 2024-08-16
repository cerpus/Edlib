import { apiClients } from '../../node-utils/index.js';
import apiConfig from '../../config/apis.js';
import resource from './resource.js';

export default (req, res) => ({
    coreInternal: apiClients.coreInternal(req, apiConfig.coreInternal),
    resource: resource(),
});
