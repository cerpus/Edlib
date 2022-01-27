import React from 'react';
import { Auth0Provider, useAuth0 } from '@auth0/auth0-react';
import configContext from '../../contexts/config.js';
import AuthProvider from './AuthProvider.jsx';

const Auth0 = ({ children }) => {
    const { logoutRedirectUrl } = React.useContext(configContext);
    const { loginWithRedirect, getAccessTokenSilently, logout } = useAuth0();

    return (
        <AuthProvider
            onLogin={() => loginWithRedirect()}
            onLoginCallback={async () => {
                return await getAccessTokenSilently({
                    ignoreCache: true,
                });
            }}
            onLogout={() =>
                logout({
                    returnTo: logoutRedirectUrl,
                })
            }
            onLogoutCallback={async () => {}}
        >
            {children}
        </AuthProvider>
    );
};

const Auth0Wrapper = ({ children }) => {
    const { authServiceSettings: settings, loginRedirectUrl } =
        React.useContext(configContext);

    return (
        <Auth0Provider
            domain={settings.domain}
            clientId={settings.clientId}
            redirectUri={loginRedirectUrl}
            audience="edlib"
            scope="superadmin"
        >
            <Auth0>{children}</Auth0>
        </Auth0Provider>
    );
};

export default Auth0Wrapper;
