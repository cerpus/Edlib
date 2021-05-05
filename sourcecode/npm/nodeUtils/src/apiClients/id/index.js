import axios from 'axios';
import { exceptionTranslator } from '../../services/index.js';
import upperCaseFirstLetter from '../../helpers/upperCaseFirstLetter.js';
import lowerCaseFirstLetter from '../../helpers/lowerCaseFirstLetter.js';
import * as errorReporting from '../../services/errorReporting.js';

const createEdlibIdAxios = (req, config) => async (options) => {
    try {
        let headers = {
            ...options.headers,
            ...errorReporting.getTraceHeaders(req),
        };

        return await axios({
            ...options,
            url: `${config.url}${options.url}`,
            headers: headers,
            maxRedirects: 0,
            timeout: 5000,
        });
    } catch (e) {
        exceptionTranslator(e, 'Id API');
    }
};

const format = (data) => {
    if (!data) {
        return data;
    }

    return {
        ...data,
        externalSystemName: upperCaseFirstLetter(data.externalSystemName),
    };
};

export default (req, config) => {
    const edlibIdAxios = createEdlibIdAxios(req, config);

    const home = async () => {
        return (
            await edlibIdAxios({
                url: `/`,
                method: 'GET',
            })
        ).data;
    };

    const getForExternal = async (externalSystemName, externalSystemId) => {
        return format(
            (
                await edlibIdAxios({
                    url: `/v1/external/${lowerCaseFirstLetter(
                        externalSystemName
                    )}/${externalSystemId}`,
                    method: 'GET',
                })
            ).data
        );
    };

    const getForId = async (edlibId) => {
        return format(
            (
                await edlibIdAxios({
                    url: `/v1/edlib/${edlibId}`,
                    method: 'GET',
                })
            ).data
        );
    };

    return { home, getForId, getForExternal, config };
};
