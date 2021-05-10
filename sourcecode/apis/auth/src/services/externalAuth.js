import JsonWebToken from 'jsonwebtoken';
import jwksClient from 'jwks-rsa';
import externalTokenVerifierConfig from '../config/externalTokenVerifier.js';

const client = jwksClient({
    strictSsl: false,
    jwksUri: externalTokenVerifierConfig.wellKnownEndpoint,
    timeout: 2000,
});

const getKeyFromAuth = (header, callback) => {
    client.getSigningKey(header.kid, function (err, key) {
        console.error(err);
        if (err) {
            return callback(err);
        }

        callback(null, key.publicKey || key.rsaPublicKey);
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
