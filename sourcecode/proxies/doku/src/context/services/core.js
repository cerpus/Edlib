// @todo remove this file!
import appConfig from '../../config/app.js';
import axios from 'axios';
import { UnauthorizedException } from '@cerpus/edlib-node-utils/exceptions/index.js';

const coreAxios = (req) => async (options) => {
    try {
        const headers = { ...options.headers };
        if (req.authorizationJwt) {
            headers.authorization = `Bearer ${req.authorizationJwt}`;
        }

        return await axios({
            ...options,
            url: `${appConfig.coreUrl}${options.url}`,
            headers,
            maxRedirects: 0,
        });
    } catch (e) {
        if (!e.response) {
            throw e;
        }

        if ([302, 401].indexOf(e.response.status) !== -1) {
            throw new UnauthorizedException();
        }

        throw {
            service: 'core-axios',
            response: {
                data: e.response.data,
            },
        };
    }
};

export default (req) => {
    const core = coreAxios(req);

    const getDokuLtiLaunch = async (token) => {
        return (
            await core({
                url: `/v2/dokulaunch`,
                method: 'GET',
                params: {
                    token,
                },
            })
        ).data;
    };

    return {
        getDokuLtiLaunch,
    };
};
