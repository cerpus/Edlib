import { apiClients } from '@cerpus/edlib-node-utils';
import apiConfig from '../../config/apis.js';
import resource from './resource.js';

export default (req, res) => ({
    coreInternal: apiClients.coreInternal(req, apiConfig.coreInternal),
    resource: resource(),
});
