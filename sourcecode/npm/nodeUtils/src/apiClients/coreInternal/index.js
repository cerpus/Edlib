import axios from 'axios';
import resource from './resource.js';
import { exceptionTranslator } from '../../services/index.js';
import * as errorReporting from '../../services/errorReporting.js';
import doku from './doku.js';

const coreAxios = (req, config) => async (options) => {
    try {
        return await axios({
            ...options,
            url: `${config.url}${options.url}`,
            maxRedirects: 0,
            headers: {
                ...options.headers,
                ...errorReporting.getTraceHeaders(req),
            },
        });
    } catch (e) {
        exceptionTranslator(e, 'Core internal API');
    }
};

export default (req, config) => {
    const core = coreAxios(req, config);

    return {
        resource: resource(core),
        doku: doku(core),
        config,
    };
};
