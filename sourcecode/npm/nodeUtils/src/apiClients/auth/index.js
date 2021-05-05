import axios from 'axios';
import AwaitLock from 'await-lock';
import moment from 'moment';
import logger from '../../services/logger.js';
import JsonWebToken from 'jsonwebtoken';
import jwksClient from 'jwks-rsa';
import * as errorReporting from '../../services/errorReporting.js';

let cachedToken = null;
let jwksClients = {};

const createAuthAxios = (req, config) => async (options) =>
    axios({
        ...options,
        url: `${config.url}${options.url}`,
        maxRedirects: 0,
        headers: {
            ...options.headers,
            ...errorReporting.getTraceHeaders(req),
        },
    });

const authMutex = AwaitLock.default ? new AwaitLock.default() : new AwaitLock();

export default (req, config) => {
    const authAxios = createAuthAxios(req, config);

    const getKeyFromAuth = (header, callback) => {
        if (!jwksClients[config.url]) {
            jwksClients[config.url] = jwksClient({
                strictSsl: false,
                jwksUri: `${config.url}/.well-known/jwks.json`,
                timeout: 2000,
            });
        }

        jwksClients[config.url].getSigningKey(header.kid, function (err, key) {
            if (err) {
                return callback(err);
            }

            callback(null, key.publicKey || key.rsaPublicKey);
        });
    };

    const verifyTokenAgainstAuth = (token, options = {}) => {
        return new Promise((resolve, reject) => {
            JsonWebToken.verify(
                token,
                getKeyFromAuth,
                options,
                (err, decoded) => {
                    if (err) {
                        return reject(err);
                    }

                    resolve(decoded);
                }
            );
        });
    };

    const loginCallback = async (code, redirectUrl) => {
        const response = await authAxios({
            url: `/oauth/token?client_id=${config.clientId}&code=${code}&redirect_uri=${redirectUrl}&grant_type=authorization_code`,
            method: 'GET',
            headers: {
                Authorization: `Basic ${Buffer.from(
                    `${config.clientId}:${config.secret}`
                ).toString('base64')}`,
            },
        });

        return response.data;
    };

    const generateJwt = async (accessToken) => {
        const response = await authAxios({
            url: `/v1/jwt/create`,
            method: 'POST',
            headers: {
                Authorization: `Bearer ${accessToken}`,
            },
        });

        return response.data;
    };

    const refreshJwt = async (accessToken) => {
        const response = await authAxios({
            url: `/v2/jwt/refresh`,
            method: 'POST',
            headers: {
                Authorization: `Bearer ${accessToken}`,
            },
        });

        return response.data;
    };

    const identity = async (token) => {
        const response = await authAxios({
            url: `/v1/identity?access_token=${token}`,
            method: 'GET',
        });

        return response.data;
    };

    const getOAuthToken = async (bypassCache = false) => {
        await authMutex.acquireAsync();

        if (
            !bypassCache &&
            cachedToken &&
            cachedToken.expiresAt.isAfter(moment())
        ) {
            authMutex.release();
            return cachedToken.access_token;
        }

        logger.info(
            `Fetching new oauth server to server token from ${config.url}`
        );

        try {
            const authResponse = await axios({
                url: `${config.url}/oauth/token`,
                method: 'POST',
                headers: {
                    authorization: `Basic ${new Buffer(
                        `${config.clientId}:${config.secret}`
                    ).toString('base64')}`,
                },
                params: { grant_type: 'client_credentials' },
            });

            cachedToken = {
                ...authResponse.data,
                expiresAt: moment().add(
                    authResponse.data.expires_in - 60 * 2, // subtract 2 minutes in seconds to have a margin
                    'seconds'
                ),
            };
        } catch (e) {
            authMutex.release();
            throw e;
        }

        authMutex.release();

        return cachedToken.access_token;
    };

    return {
        loginCallback,
        identity,
        getOAuthToken,
        generateJwt,
        refreshJwt,
        verifyTokenAgainstAuth,
        config,
    };
};
