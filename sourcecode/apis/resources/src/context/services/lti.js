import apis from '../../config/apis.js';
import axios from 'axios';
import { exceptionTranslator } from '@cerpus/edlib-node-utils';

const ltiAxios = async (options) => {
    try {
        return await axios({
            timeout: 5000,
            maxRedirects: 0,
            ...options,
            url: `${apis.lti.url}${options.url}`,
        });
    } catch (e) {
        exceptionTranslator(e);
    }
};

const getUsageViews = async (
    offset = 0,
    limit = 100,
    hideTotalCount = false
) => {
    return (
        await ltiAxios({
            url: `/v1/usage-views`,
            method: 'GET',
            params: {
                limit,
                offset,
                hideTotalCount,
            },
            timeout: 120000,
        })
    ).data;
};

export default () => {
    return {
        getUsageViews,
    };
};
