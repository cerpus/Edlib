import apis from '../../config/apis.js';
import axios from 'axios';
import { exceptionTranslator } from '@cerpus/edlib-node-utils';

const urlAxios = async (options) => {
    try {
        return await axios({
            ...options,
            url: `${apis.url.url}${options.url}`,
            maxRedirects: 0,
            timeout: 30000,
        });
    } catch (e) {
        exceptionTranslator(e);
    }
};

const getSyncJobStatus = async (jobId) => {
    return (
        await urlAxios({
            url: `/v1/sync-resources/${jobId}`,
            method: 'GET',
        })
    ).data;
};

const syncResources = async () => {
    return (
        await urlAxios({
            url: `/v1/sync-resources`,
            method: 'POST',
        })
    ).data;
};

const getByIdWithEmbedInfo = async (urlId) => {
    return (
        await urlAxios({
            url: `/v1/urls/${urlId}?embedInfo=1`,
            method: 'GET',
        })
    ).data;
};

export default () => {
    return {
        getSyncJobStatus,
        syncResources,
        getByIdWithEmbedInfo,
    };
};
