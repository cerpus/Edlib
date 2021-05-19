import apiConfig from '../../config/apis.js';
import core from '@cerpus-private/edlib-node-utils/apiClients/coreInternal/index.js';
import version from '@cerpus/edlib-node-utils/apiClients/version/index.js';
import externalResourceFetcher from './externalResourceFetcher.js';
import elasticsearch from './elasticsearch/index.js';

export default (req, res) => {
    const versionApi = version(req, apiConfig.version);

    return {
        version: versionApi,
        externalResourceFetcher: externalResourceFetcher(req),
        elasticsearch: elasticsearch(req),
        coreInternal: core(req, apiConfig.coreInternal),
    };
};
