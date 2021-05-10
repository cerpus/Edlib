import axios from 'axios';
import { NotFoundException } from '@cerpus/edlib-node-utils/exceptions/index.js';
import apiConfig from '../../config/apis.js';
import { exceptionTranslator } from '@cerpus/edlib-node-utils/services/index.js';

const getUrl = (externalSystemName) => {
    const externalApiConfig =
        apiConfig.externalResourceAPIS[externalSystemName.toLowerCase()];

    if (!externalApiConfig) {
        throw new NotFoundException('externalSystemName');
    }

    return externalApiConfig.url;
};

const createAxios = (req) => async (url, options = {}) => {
    try {
        return await axios({
            ...options,
            url,
            maxRedirects: 0,
        });
    } catch (e) {
        console.error(e.response);
        exceptionTranslator(e, url + ' API');
    }
};

export default (req) => {
    const request = createAxios(req);

    const getById = async (externalSystemName, externalSystemId) => {
        return (
            await request(`${getUrl(externalSystemName)}/${externalSystemId}`)
        ).data;
    };

    const getAll = async (externalSystemName, params) => {
        return (
            await request(getUrl(externalSystemName), {
                params,
            })
        ).data;
    };

    return {
        getById,
        getAll,
    };
};
