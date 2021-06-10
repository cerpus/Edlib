import axios from 'axios';
import {
    NotFoundException,
    AxiosException,
    exceptionTranslator,
    logger,
} from '@cerpus/edlib-node-utils';
import apiConfig from '../../config/apis.js';

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

const delay = (time) => new Promise((resolve) => setTimeout(resolve, time));

const autoRetryOnTimeout = (
    fnc,
    options = {
        coolDownTime: 5000,
        numberOfRetries: 3,
    },
    iteration = 0
) => async (...params) => {
    try {
        return await fnc(...params);
    } catch (e) {
        if (
            iteration < options.numberOfRetries &&
            e instanceof AxiosException
        ) {
            logger.info(
                'Request timed out. Retrying after cooldown of ' +
                    options.coolDownTime
            );
            await delay(options.coolDownTime);

            return autoRetryOnTimeout(fnc, options, iteration + 1)(...params);
        }

        throw e;
    }
};

export default (req) => {
    const request = createAxios(req);

    const getById = async (externalSystemName, externalSystemId) => {
        return (
            await request(`${getUrl(externalSystemName)}/${externalSystemId}`)
        ).data;
    };

    const getAll = autoRetryOnTimeout(async (externalSystemName, params) => {
        return (
            await request(getUrl(externalSystemName), {
                params,
            })
        ).data;
    });

    return {
        getById,
        getAll: getAll,
    };
};
