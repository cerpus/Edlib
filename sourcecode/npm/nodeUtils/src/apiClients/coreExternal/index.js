import axios from 'axios';
import recommendations from './recommendations.js';
import links from './links.js';
import preview from './preview.js';
import resourceFilters from './resourceFilters.js';
import contentAuthor from './contentAuthor.js';
import version from './version.js';
import resource from './resource.js';
import { exceptionTranslator } from '../../services/index.js';
import license from './license.js';
import info from './info.js';
import doku from './doku.js';
import * as errorReporting from '../../services/errorReporting.js';

const coreAxios = (req, config) => async (options) => {
    try {
        let headers = {
            ...options.headers,
            ...errorReporting.getTraceHeaders(req),
        };
        if (req.authorizationJwt) {
            headers.authorization = `Bearer ${req.authorizationJwt}`;
        }

        return await axios({
            ...options,
            url: `${config.url}${options.url}`,
            headers,
            maxRedirects: 0,
        });
    } catch (e) {
        exceptionTranslator(e, 'Core external API');
    }
};

export default (req, config) => {
    const core = coreAxios(req, config);

    return {
        recommendations: recommendations(core),
        links: links(core),
        preview: preview(core),
        resourceFilters: resourceFilters(core),
        contentAuthor: contentAuthor(core),
        version: version(core),
        resource: resource(core),
        license: license(core),
        info: info(core),
        doku: doku(core),
        config,
    };
};
