import apiConfig from '../../config/apis.js';
import { apiClients } from '@cerpus/edlib-node-utils';
import embedly from './embedly.js';

export default (req, res) => {
    const versionApi = apiClients.version(req, apiConfig.version);

    return {
        coreInternal: apiClients.coreInternal(req, apiConfig.coreInternal),
        version: versionApi,
        embedly: embedly(),
    };
};
