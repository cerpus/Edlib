import axios from 'axios';
import * as errorReporting from '../../services/errorReporting.js';
import { verifyTokenAgainstAuth } from '../../services/auth';

let jwksClients = {};

const createAxios = (req, config) => async (options) =>
    axios({
        ...options,
        url: `${config.url}${options.url}`,
        maxRedirects: 0,
        headers: {
            ...options.headers,
            ...errorReporting.getTraceHeaders(req),
        },
    });

export default (req, config) => {
    const authAxios = createAxios(req, config);

    const convertToken = async (externalToken) => {
        return (
            await authAxios({
                url: `/v1/convert-token`,
                method: 'POST',
                data: {
                    externalToken,
                },
            })
        ).data;
    };

    const refreshToken = async (token) => {
        return (
            await authAxios({
                url: `/v1/refresh-token`,
                method: 'POST',
                data: {
                    token,
                },
            })
        ).data;
    };

    const getUsersByEmail = async (emails) => {
        return (
            await authAxios({
                url: `/v1/users-by-email`,
                method: 'POST',
                data: {
                    emails,
                },
            })
        ).data;
    };

    const getAuthServiceInfo = async () => {
        return (
            await authAxios({
                url: `/v1/auth-service-info`,
                method: 'GET',
            })
        ).data;
    };

    const cerpusAuthLoginCallback = async (code, callbackUrl) => {
        return (
            await authAxios({
                url: `/v1/cerpusauth/login/callback`,
                method: 'GET',
                data: {
                    params: {
                        code,
                        callbackUrl,
                    },
                },
            })
        ).data;
    };

    return {
        convertToken,
        refreshToken,
        getUsersByEmail,
        cerpusAuthLoginCallback,
        getAuthServiceInfo,
        verifyTokenAgainstAuth: verifyTokenAgainstAuth(
            jwksClients,
            `${config.url}/.well-known/jwks.json`
        ),
        config,
    };
};
