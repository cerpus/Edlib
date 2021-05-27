import React from 'react';

import useFetch from '../hooks/useFetch.jsx';
import ConfigContext from '../contexts/config.js';

const AuthProviderContainer = ({ children }) => {
    const { loading, response: authServiceInfo } = useFetch(
        '/auth/v1/auth-service-info'
    );

    const defaultConfigContextValues = React.useContext(ConfigContext);

    if (loading) {
        return <></>;
    }

    return (
        <ConfigContext.Provider
            value={{
                ...defaultConfigContextValues,
                authUrl: authServiceInfo.url,
                authClientId: authServiceInfo.clientId,
            }}
        >
            {children}
        </ConfigContext.Provider>
    );
};

export default AuthProviderContainer;
