import JsonWebToken from 'jsonwebtoken';
import jwksClient from 'jwks-rsa';
import getExternalTokenVerifierConfig from '../config/externalTokenVerifier.js';

let jwksClients = {};

const getKeyFromAuth = (header, callback) => {
    getExternalTokenVerifierConfig().then((externalTokenVerifierConfig) => {
        if (!jwksClients[externalTokenVerifierConfig.wellKnownEndpoint]) {
            jwksClients[
                externalTokenVerifierConfig.wellKnownEndpoint
            ] = jwksClient({
                strictSsl: false,
                jwksUri: externalTokenVerifierConfig.wellKnownEndpoint,
                timeout: 2000,
            });
        }

        jwksClients[
            externalTokenVerifierConfig.wellKnownEndpoint
        ].getSigningKey(header.kid, function (err, key) {
            if (err) {
                return callback(err);
            }

            callback(null, key.publicKey || key.rsaPublicKey);
        });
    });
};

const verifyToken = (token, options = {}) =>
    new Promise((resolve, reject) => {
        JsonWebToken.verify(token, getKeyFromAuth, options, (err, decoded) => {
            if (err) {
                return reject(err);
            }

            resolve(decoded);
        });
    });

export default { verifyToken };
