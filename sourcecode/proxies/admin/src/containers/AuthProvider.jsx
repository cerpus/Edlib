import React from 'react';

import useFetch from '../hooks/useFetch.jsx';
import AuthContext from '../contexts/auth.js';
import configContext from '../contexts/config.js';
import request from '../helpers/request.js';
import store from 'store';
import storageKeys from '../constants/storageKeys.js';

const AuthProviderContainer = ({ children }) => {
    const fetch = async () => {};
    const { authUrl, authClientId, loginRedirectUrl } = React.useContext(
        configContext
    );
    const { loading, response: user, setResponse } = useFetch('/auth/v1/me');

    const authPath = '/oauth/authorize';
    const loginUrl = `${authUrl}${authPath}?client_id=${authClientId}&redirect_uri=${loginRedirectUrl}&response_type=code&scope=read&state=%`;

    React.useEffect(() => {
        if (!user) {
            return;
        }

        const intervalId = setInterval(() => {
            const refreshToken = store.get(storageKeys.REFRESH_TOKEN);
            if (!refreshToken) {
                return setResponse(null);
            }

            request('/auth/v1/jwt/refresh', 'GET', {
                query: {
                    refresh_token: refreshToken,
                },
            })
                .then(({ authToken }) => {
                    store.set(storageKeys.AUTH_TOKEN, authToken);
                })
                .catch(() => {
                    setResponse(null);
                });
        }, 1000 * 60);

        return () => clearInterval(intervalId);
    }, [user]);

    return (
        <AuthContext.Provider
            value={{
                isAuthenticated: !!user,
                isAuthenticating: loading,
                user,
                refetch: fetch,
                logout: () => {
                    setResponse(null);
                },
                login: ({ user, authToken, refreshToken }) => {
                    setResponse(user);
                    store.set(storageKeys.AUTH_TOKEN, authToken);
                    store.set(storageKeys.REFRESH_TOKEN, refreshToken);
                },
                loginUrl,
            }}
        >
            {children}
        </AuthContext.Provider>
    );
};

export default AuthProviderContainer;
