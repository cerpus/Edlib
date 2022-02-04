import { env } from '@cerpus/edlib-node-utils';
import externalAuthService from '../services/externalAuth.js';

const externalTokenVerifierConfig = {
    adapter: env('EDLIBCOMMON_EXTERNALAUTH_ADAPTER'),
    wellKnownEndpoint: env('EDLIBCOMMON_EXTERNALAUTH_JWKS_ENDPOINT'),
    issuer: env('EDLIBCOMMON_EXTERNALAUTH_ISSUER'),
    auth0: {
        domain: env('EDLIBCOMMON_EXTERNALAUTH_ADAPTER_AUTH0_DOMAIN'),
        clientId: env('EDLIBCOMMON_EXTERNALAUTH_ADAPTER_AUTH0_CLIENTID'),
        audience: env('EDLIBCOMMON_EXTERNALAUTH_ADAPTER_AUTH0_AUDIENCE'),
        propertyPaths: {
            id: env('EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_ID'),
            name: env('EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_NAME'),
            email: env('EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_EMAIL'),
        },
    },
    cerpusAuth: {
        url: env(
            'EDLIBCOMMON_EXTERNALAUTH_ADAPTER_CERPUSAUTH_URL',
            env('AUTHAPI_URL')
        ),
        clientId: env(
            'EDLIBCOMMON_EXTERNALAUTH_ADAPTER_CERPUSAUTH_CLIENTID',
            env('AUTHAPI_CLIENT_ID')
        ),
        secret: env(
            'EDLIBCOMMON_EXTERNALAUTH_ADAPTER_CERPUSAUTH_SECRET',
            env('AUTHAPI_SECRET')
        ),
    },
};

externalAuthService.getConfiguration(externalTokenVerifierConfig);

export default externalTokenVerifierConfig;
