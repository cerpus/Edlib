import apis from '../../config/apis.js';
import axios from 'axios';
import { exceptionTranslator } from '@cerpus/edlib-node-utils';

const resourceAxios = async (options) => {
    try {
        return await axios({
            timeout: 5000,
            maxRedirects: 0,
            ...options,
            url: `${apis.resource.url}${options.url}`,
        });
    } catch (e) {
        exceptionTranslator(e);
    }
};

const getResourcesByExternalIdReferences = async (externalSystemReferences) => {
    return (
        await resourceAxios({
            url: `/v1/resources/by-external-references`,
            method: 'POST',
            data: {
                externalSystemReferences,
            },
        })
    ).data;
};

export default () => {
    return {
        getResourcesByExternalIdReferences,
    };
};
