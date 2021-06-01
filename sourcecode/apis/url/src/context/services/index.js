import apiConfig from '../../config/apis.js';
import core from '@cerpus/edlib-node-utils/apiClients/coreInternal/index.js';
import version from '@cerpus/edlib-node-utils/apiClients/version/index.js';
import embedly from './embedly.js';

export default (req, res) => {
    const versionApi = version(req, apiConfig.version);

    return {
        coreInternal: core(req, apiConfig.coreInternal),
        version: versionApi,
        embedly: embedly(),
    };
};
