export default {
    wellKnownEndpoint: process.env.EDLIBCOMMON_EXTERNALAUTH_JWKS_ENDPOINT,
    propertyPaths: {
        id: process.env.EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_ID,
        firstName: process.env.EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_FIRSTNAME,
        lastName: process.env.EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_LASTNAME,
        email: process.env.EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_EMAIL,
    },
};
