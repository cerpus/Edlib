import { env } from '@cerpus/edlib-node-utils';

const adapter = env('EDLIBCOMMON_EXTERNALAUTH_ADAPTER', 'auth0');

const getAuth0AdapterSettings = () => {
    const publicSettings = {
        domain: env('EDLIBCOMMON_EXTERNALAUTH_ADAPTER_AUTH0_DOMAIN'),
        clientId: env('EDLIBCOMMON_EXTERNALAUTH_ADAPTER_AUTH0_CLIENTID'),
        audience: env('EDLIBCOMMON_EXTERNALAUTH_ADAPTER_AUTH0_AUDIENCE'),
    };

    if (
        !publicSettings.domain ||
        !publicSettings.clientId ||
        !publicSettings.audience
    ) {
        return false;
    }

    return {
        public: publicSettings,
        private: {},
    };
};

const getCerpusAuthAdapterSettings = () => {
    const publicSettings = {
        url: env(
            'EDLIBCOMMON_EXTERNALAUTH_ADAPTER_CERPUSAUTH_URL',
            env('AUTHAPI_URL')
        ),
        clientId: env(
            'EDLIBCOMMON_EXTERNALAUTH_ADAPTER_CERPUSAUTH_CLIENTID',
            env('AUTHAPI_CLIENT_ID')
        ),
    };
    const privateSettings = {
        secret: env(
            'EDLIBCOMMON_EXTERNALAUTH_ADAPTER_CERPUSAUTH_SECRET',
            env('AUTHAPI_SECRET')
        ),
    };

    if (
        !publicSettings.url ||
        !publicSettings.clientId ||
        !privateSettings.secret
    ) {
        return false;
    }

    return {
        public: publicSettings,
        private: privateSettings,
    };
};

const getAdapterSettings = () => {
    if (adapter === 'auth0') {
        return getAuth0AdapterSettings();
    }
    if (adapter === 'cerpusauth') {
        return getCerpusAuthAdapterSettings();
    }

    return false;
};

if (!getAdapterSettings()) {
    throw new Error('Invalid external auth adapter settings');
}

export default {
    externalAuth: {
        adapter,
        adapterSettings: getAdapterSettings(),
    },
    edlibAuth: {
        url: env('EDLIB_AUTH_URL', 'http://authapi'),
    },
};
