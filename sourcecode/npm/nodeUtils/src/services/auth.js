import JsonWebToken, { JsonWebTokenError } from 'jsonwebtoken';
import jwksClient from 'jwks-rsa';

const getKeyFromAuth = (jwksClients, url) => (header, callback) => {
    if (!jwksClients[url]) {
        jwksClients[url] = jwksClient({
            strictSsl: false,
            jwksUri: url,
            timeout: 2000,
        });
    }

    jwksClients[url].getSigningKey(header.kid, function (err, key) {
        if (err) {
            return callback(err);
        }

        callback(null, key.publicKey || key.rsaPublicKey);
    });
};

export const verifyTokenAgainstAuth = (jwksClients, url) => (
    token,
    options = {}
) =>
    new Promise((resolve, reject) => {
        JsonWebToken.verify(
            token,
            getKeyFromAuth(jwksClients, url),
            options,
            (err, decoded) => {
                if (err) {
                    if (err instanceof JsonWebTokenError) {
                        return resolve(null);
                    }
                    return reject(err);
                }

                resolve(decoded);
            }
        );
    });
