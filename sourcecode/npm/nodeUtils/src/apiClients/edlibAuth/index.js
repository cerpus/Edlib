import axios from 'axios';
import JsonWebToken from 'jsonwebtoken';
import jwksClient from 'jwks-rsa';
import * as errorReporting from '../../services/errorReporting.js';

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

    return {
        convertToken,
        refreshToken,
        verifyTokenAgainstAuth,
        config,
    };
};
