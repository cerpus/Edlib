import apiConfig from '../config/apis.js';
import {
    NotFoundException,
    ApiException,
} from '@cerpus/edlib-node-utils/exceptions/index.js';

const getConfig = (externalSystemName) => {
    const config = apiConfig.externalResourceAPIS[externalSystemName];

    if (!config) {
        throw new NotFoundException(externalSystemName);
    }

    return config;
};

export default {
    getConfig,
    getLtiResourceInfo: (resourceVersion) => {
        const config = getConfig(resourceVersion.externalSystemName);

        if (!config.ltiUrl) {
            throw new ApiException(
                `Missing ltiUrl in configuration of external system ${resourceVersion.externalSystemName}`
            );
        }

        return {
            url: `${config.ltiUrl}/${resourceVersion.externalSystemId}`,
            consumerSecret: config.ltiConsumerSecret,
            consumerKey: config.ltiConsumerKey,
            resourceVersion,
        };
    },
    getLtiCreateInfo: (externalSystemName, group) => {
        const config = getConfig(externalSystemName);

        if (!config.ltiUrl) {
            throw new ApiException(
                `Missing ltiUrl in configuration of external system ${externalSystemName}`
            );
        }

        let url = `${config.ltiUrl}/create`;
        if (group) {
            url += `/${group}`;
        }

        return {
            url: url.toString(),
            consumerSecret: config.ltiConsumerSecret,
            consumerKey: config.ltiConsumerKey,
        };
    },
};
