import apiConfig from '../../config/apis.js';
import version from '@cerpus/edlib-node-utils/apiClients/version/index.js';
import externalResourceFetcher from './externalResourceFetcher.js';
import elasticsearch from './elasticsearch/index.js';

export default (req, res) => {
    const versionApi = version(req, apiConfig.version);

    return {
        version: versionApi,
        externalResourceFetcher: externalResourceFetcher(req),
        elasticsearch: elasticsearch(req),
    };
};
