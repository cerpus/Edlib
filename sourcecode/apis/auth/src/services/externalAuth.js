import JsonWebToken from 'jsonwebtoken';
import jwksClient from 'jwks-rsa';
import externalTokenVerifierConfig from '../config/externalTokenVerifier.js';
import _ from 'lodash';

let jwksClients = {};

const getKeyFromAuth = (header, callback) => {
    if (!jwksClients[externalTokenVerifierConfig.wellKnownEndpoint]) {
        jwksClients[externalTokenVerifierConfig.wellKnownEndpoint] = jwksClient(
            {
                strictSsl: false,
                jwksUri: externalTokenVerifierConfig.wellKnownEndpoint,
                timeout: 2000,
            }
        );
    }

    jwksClients[externalTokenVerifierConfig.wellKnownEndpoint].getSigningKey(
        header.kid,
        function (err, key) {
            if (err) {
                return callback(err);
            }

            callback(null, key.publicKey || key.rsaPublicKey);
        }
    );
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

const getUserDataFromToken = (payload) => {
    let firstName, lastName, isAdmin;

    if (externalTokenVerifierConfig.propertyPaths.name) {
        const fullName = _.get(
            payload,
            externalTokenVerifierConfig.propertyPaths.name
        );

        if (fullName.split(' ').length > 1) {
            const lastIndexOfSpace = fullName.lastIndexOf(' ');
            lastName = fullName.substring(lastIndexOfSpace + 1);
            firstName = fullName.substring(0, lastIndexOfSpace);
        } else {
            firstName = fullName;
            lastName = '';
        }
    } else {
        firstName = _.get(
            payload,
            externalTokenVerifierConfig.propertyPaths.firstName
        );
        lastName = _.get(
            payload,
            externalTokenVerifierConfig.propertyPaths.lastName
        );
    }

    if (!externalTokenVerifierConfig.propertyPaths.isAdminMethod) {
        isAdmin = _.get(
            payload,
            externalTokenVerifierConfig.propertyPaths.isAdmin
        );
    } else {
        isAdmin =
            _.get(
                payload,
                externalTokenVerifierConfig.propertyPaths.isAdminInScopeKey
            )
                .split(' ')
                .indexOf(
                    externalTokenVerifierConfig.propertyPaths
                        .isAdminInScopeValue
                ) !== -1;
    }

    return {
        id: _.get(payload, externalTokenVerifierConfig.propertyPaths.id),
        email: _.get(payload, externalTokenVerifierConfig.propertyPaths.email),
        firstName,
        lastName,
        isAdmin,
    };
};

export default { verifyToken, getUserDataFromToken };
