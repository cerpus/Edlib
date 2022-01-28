import React from 'react';
import AuthProvider from './AuthProvider.jsx';
import configContext from '../../contexts/config.js';
import queryString from 'query-string';
import request from '../../helpers/request.js';

const CerpusAuth = ({ children }) => {
    const {
        authServiceSettings: settings,
        loginRedirectUrl,
        logoutRedirectUrl,
    } = React.useContext(configContext);

    return (
        <AuthProvider
            onLogin={() =>
                (window.location.href = `${settings.url}/oauth/authorize?client_id=${settings.clientId}&redirect_uri=${loginRedirectUrl}&response_type=code&scope=read&state=%`)
            }
            onLogout={() =>
                (window.location.href = `${settings.url}/logout?returnUrl=${logoutRedirectUrl}`)
            }
            onLoginCallback={async () => {
                const query = queryString.parse(location.search);

                if (!query.code) {
                    return;
                }

                const { externalToken } = await request(
                    `/auth/v1/cerpusauth/login/callback?code=${query.code}&callbackUrl=${loginRedirectUrl}`,
                    'GET'
                );

                return externalToken;
            }}
            onLogoutCallback={async () => {
                history.push('/login');
            }}
        >
            {children}
        </AuthProvider>
    );
};

export default CerpusAuth;
