import apiConfig from '../../config/apis.js';
import { apiClients } from '@cerpus/edlib-node-utils';
import externalResourceFetcher from './externalResourceFetcher.js';
import elasticsearch from './elasticsearch/index.js';
import lti from './lti.js';

export default (req) => {
    const versionApi = apiClients.version(req, apiConfig.version);

    return {
        version: versionApi,
        externalResourceFetcher: externalResourceFetcher(req),
        elasticsearch: elasticsearch(req),
        coreInternal: apiClients.coreInternal(req, apiConfig.coreInternal),
        edlibAuth: apiClients.edlibAuth(req, apiConfig.edlibAuth),
        lti: lti(),
    };
};
