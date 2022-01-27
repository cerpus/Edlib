import React from 'react';
import AuthContext from '../../contexts/auth.js';
import useFetchWithToken from '../../hooks/useFetchWithToken.jsx';
import { useHistory } from 'react-router-dom';
import { useTokenContext } from '../../contexts/token.js';

const AuthProviderWithoutJwt = ({ children, onLoginCallback, onLogin }) => {
    const history = useHistory();
    const { updateExternalToken, jwt } = useTokenContext();

    return (
        <AuthContext.Provider
            value={{
                isAuthenticated: false,
                isAuthenticating: false,
                user: null,
                refetch: () => {},
                onLoginCallback: async () => {
                    const token = await onLoginCallback();
                    updateExternalToken(token);
                    history.push('/');
                },
                onLogoutCallback: async () => {
                    history.push('/');
                },
                onLogin,
                onLogout: async () => {},
            }}
        >
            {children}
        </AuthContext.Provider>
    );
};

const AuthProviderWithJwt = ({ children, onLogoutCallback, onLogout }) => {
    const history = useHistory();
    const { removeExternalToken } = useTokenContext();

    const {
        loading,
        response: user,
        refetch,
    } = useFetchWithToken(
        '/auth/v1/me',
        'GET',
        React.useMemo(() => ({}), [])
    );

    return (
        <AuthContext.Provider
            value={{
                isAuthenticated: !!user,
                isAuthenticating: loading,
                user,
                refetch,
                onLoginCallback: async () => {
                    history.push('/');
                },
                onLogoutCallback: async () => {
                    removeExternalToken();
                    await onLogoutCallback();
                    history.push('/login');
                },
                onLogin: async () => {},
                onLogout,
            }}
        >
            {children}
        </AuthContext.Provider>
    );
};

const AuthProvider = ({
    children,
    onLogin,
    onLoginCallback,
    onLogout,
    onLogoutCallback,
}) => {
    const { jwt } = useTokenContext();

    if (jwt.loading) {
        return (
            <AuthContext.Provider
                value={{
                    isAuthenticated: false,
                    isAuthenticating: true,
                    user: null,
                }}
            >
                {children}
            </AuthContext.Provider>
        );
    }

    if (!jwt.value) {
        return (
            <AuthProviderWithoutJwt
                children={children}
                onLoginCallback={onLoginCallback}
                onLogin={onLogin}
            />
        );
    }

    return (
        <AuthProviderWithJwt
            children={children}
            onLogout={onLogout}
            onLogoutCallback={onLogoutCallback}
        />
    );
};

export default AuthProvider;
