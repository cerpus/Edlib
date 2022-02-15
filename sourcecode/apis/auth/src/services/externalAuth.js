import JsonWebToken from 'jsonwebtoken';
import jwksClient from 'jwks-rsa';
import auth0 from './externalAuthAdatpers/auth0.js';
import cerpusAuth from './externalAuthAdatpers/cerpusAuth.js';
import {
    ValidationException,
    validationExceptionError,
} from '@cerpus/edlib-node-utils';
import generic from './externalAuthAdatpers/generic.js';

let jwksClients = {};

const getKeyFromAuth = (uri) => (header, callback) => {
    if (!jwksClients[uri]) {
        jwksClients[uri] = jwksClient({
            strictSsl: false,
            jwksUri: uri,
            timeout: 2000,
        });
    }

    jwksClients[uri].getSigningKey(header.kid, function (err, key) {
        if (err) {
            return callback(err);
        }

        callback(null, key.publicKey || key.rsaPublicKey);
    });
};

const verifyToken = (uri, token, options = {}) =>
    new Promise((resolve, reject) => {
        JsonWebToken.verify(
            token,
            getKeyFromAuth(uri),
            options,
            (err, decoded) => {
                if (err) {
                    return reject(err);
                }

                resolve(decoded);
            }
        );
    });

const getAdapterFunctions = (adapter) => {
    switch (adapter) {
        case 'auth0':
            return auth0();
        case 'cerpusAuth':
            return cerpusAuth();
        case 'generic':
            return generic();
    }

    throw new ValidationException(
        validationExceptionError(
            'adapter',
            'body',
            'Unknown adapter ' + adapter
        )
    );
};

const getUserDataFromToken = (adapter, payload, propertyPaths) =>
    getAdapterFunctions(adapter).getUserDataFromToken(payload, propertyPaths);

const getPropertyPaths = async (context, tenantAuthMethod) => {
    const defaultPropertyPaths = await getAdapterFunctions(
        tenantAuthMethod.adapter
    ).getDefaultPropertyPaths();

    let names = {};

    if (tenantAuthMethod.propertyPathName) {
        names.name = tenantAuthMethod.propertyPathName;
    } else if (
        tenantAuthMethod.propertyPathFirstName &&
        tenantAuthMethod.propertyPathLastName
    ) {
        names = {
            firstName: tenantAuthMethod.propertyPathFirstName,
            lastName: tenantAuthMethod.propertyPathLastName,
        };
    } else {
        names = {
            name: defaultPropertyPaths.name,
            firstName: defaultPropertyPaths.firstName,
            lastName: defaultPropertyPaths.lastName,
        };
    }

    return {
        ...names,
        id: tenantAuthMethod.propertyPathId || defaultPropertyPaths.id,
        email: tenantAuthMethod.propertyPathEmail || defaultPropertyPaths.email,
        isAdmin: defaultPropertyPaths.isAdmin,
        isAdminMethod: defaultPropertyPaths.isAdminMethod,
        isAdminInScopeKey: defaultPropertyPaths.isAdminInScopeKey,
        isAdminInScopeValue: defaultPropertyPaths.isAdminInScopeValue,
    };
};

const getConfiguration = (configuration) => {
    if (
        !configuration.wellKnownEndpoint ||
        !configuration.issuer ||
        !configuration.adapter
    ) {
        throw new Error('Missing auth configuration');
    }

    const { frontendSettings, settings } = getAdapterFunctions(
        configuration.adapter
    ).getConfiguration(configuration[configuration.adapter]);

    return {
        frontendSettings,
        settings: {
            ...settings,
            wellKnownEndpoint: configuration.wellKnownEndpoint,
            issuer: configuration.issuer,
            adapter: configuration.adapter,
        },
    };
};

export default {
    verifyToken,
    getUserDataFromToken,
    getConfiguration,
    getPropertyPaths,
};
