import apis from '../../config/apis.js';
import axios from 'axios';
import { exceptionTranslator, NotFoundException } from '../../node-utils/index.js';

const resourceAxios = async (options) => {
    try {
        return await axios({
            ...options,
            url: `${apis.resource.url}${options.url}`,
            maxRedirects: 0,
            timeout: 5000,
        });
    } catch (e) {
        exceptionTranslator(e);
    }
};

const getResourceFromExternalSystemInfo = async (
    externalSystemName,
    externalSystemId
) => {
    try {
        return (
            await resourceAxios({
                url: `/v1/resources-from-external/${externalSystemName}/${externalSystemId}`,
                method: 'GET',
            })
        ).data;
    } catch (e) {
        if (e instanceof NotFoundException) {
            return null;
        }
        throw e;
    }
};

export default () => {
    return {
        getResourceFromExternalSystemInfo,
    };
};
