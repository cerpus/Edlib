import core from '@cerpus/edlib-node-utils/apiClients/coreInternal/index.js';
import apiConfig from '../../config/apis.js';
import resource from './resource.js';

export default (req, res) => ({
    coreInternal: core(req, apiConfig.coreInternal),
    resource: resource(),
});
