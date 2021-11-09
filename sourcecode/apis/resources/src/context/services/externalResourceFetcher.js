import axios from 'axios';
import {
    NotFoundException,
    AxiosException,
    exceptionTranslator,
    logger,
    redisHelpers,
} from '@cerpus/edlib-node-utils';
import apiConfig from '../../config/apis.js';
import crypto from 'crypto';

const createAxios = () => async (
    externalSystemName,
    urlKey,
    path,
    options = {}
) => {
    const externalApiConfig =
        apiConfig.externalResourceAPIS[externalSystemName.toLowerCase()];

    if (!externalApiConfig) {
        throw new NotFoundException('externalSystemName');
    }

    try {
        let headers = options.headers || {};

        if (externalApiConfig.httpAuthKey) {
            headers = {
                ...headers,
                'x-api-key': externalApiConfig.httpAuthKey,
            };
        }

        return await axios({
            ...options,
            url: externalApiConfig.urls[urlKey] + path,
            maxRedirects: 0,
            headers,
        });
    } catch (e) {
        logger.error(e.response);
        exceptionTranslator(e, externalApiConfig.urls[urlKey] + path + ' API');
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

export default (cache = true) => {
    const request = createAxios();

    const getById = async (externalSystemName, externalSystemId) => {
        return (
            await request(externalSystemName, 'content', `/${externalSystemId}`)
        ).data;
    };

    const getAll = autoRetryOnTimeout(async (externalSystemName, params) => {
        return (
            await request(externalSystemName, 'content', '', {
                params,
            })
        ).data;
    });

    const _getContentTypeInfo = async (externalSystemName, contentType) => {
        let path = '';

        if (contentType) {
            path += `/${contentType}`;
        }

        try {
            return (await request(externalSystemName, 'contentType', path)).data
                .contentType;
        } catch (e) {
            if (!(e instanceof NotFoundException)) {
                throw e;
            }
        }

        return null;
    };

    const getContentTypeInfo = cache
        ? redisHelpers.cacheWrapper(
              (...args) =>
                  `externalResourceFetcher-getContentTypeInfo-${crypto
                      .createHash('md5')
                      .update(args.join(','))
                      .digest('hex')}`,
              _getContentTypeInfo,
              60 * 60 * 24
          )
        : _getContentTypeInfo;

    return {
        getById,
        getAll,
        getContentTypeInfo,
    };
};
