const isValid = () => {
    if (
        !process.env.EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_NAME &&
        (!process.env.EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_FIRSTNAME ||
            !process.env.EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_LASTNAME)
    ) {
        return false;
    }

    if (!process.env.EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_ID) {
        return false;
    }

    if (!process.env.EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_EMAIL) {
        return false;
    }

    if (!process.env.EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_ISADMIN_METHOD) {
        if (!process.env.EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_ISADMIN) {
            return false;
        }
    } else {
        if (
            process.env.EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_ISADMIN_METHOD !==
            'inscope'
        ) {
            return false;
        }

        if (
            !process.env
                .EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_ISADMIN_INSCOPE_KEY ||
            !process.env
                .EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_ISADMIN_INSCOPE_VALUE
        ) {
            return false;
        }
    }

    return true;
};

if (!isValid()) {
    throw new Error(
        'Missing EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH configuration'
    );
}

export default {
    wellKnownEndpoint: process.env.EDLIBCOMMON_EXTERNALAUTH_JWKS_ENDPOINT,
    propertyPaths: {
        id: process.env.EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_ID,
        name: process.env.EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_NAME,
        firstName: process.env.EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_FIRSTNAME,
        lastName: process.env.EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_LASTNAME,
        email: process.env.EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_EMAIL,
        isAdminMethod:
            process.env.EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_ISADMIN_METHOD,
        isAdminInScopeKey:
            process.env
                .EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_ISADMIN_INSCOPE_KEY,
        isAdminInScopeValue:
            process.env
                .EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_ISADMIN_INSCOPE_VALUE,
        isAdmin: process.env.EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_ISADMIN,
    },
};
