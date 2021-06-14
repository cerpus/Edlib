import apis from '../../config/apis.js';
import axios from 'axios';
import { exceptionTranslator } from '@cerpus/edlib-node-utils';

const ltiAxios = async (options) => {
    try {
        return await axios({
            ...options,
            url: `${apis.lti.url}${options.url}`,
            maxRedirects: 0,
            timeout: 5000,
        });
    } catch (e) {
        exceptionTranslator(e);
    }
};

const getUsageViews = async (offset = 0, limit = 100) => {
    return (
        await ltiAxios({
            url: `/v1/usage-views`,
            method: 'GET',
            params: {
                limit,
                offset,
            },
        })
    ).data;
};

export default () => {
    return {
        getUsageViews,
    };
};
