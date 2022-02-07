import React from 'react';
import AuthProvider from './AuthProvider.jsx';
import queryString from 'query-string';
import { useHistory, useLocation } from 'react-router-dom';

const MockAuth = ({ children }) => {
    const history = useHistory();
    const location = useLocation();

    return (
        <AuthProvider
            onLogin={() => history.push('/login/mock')}
            onLogout={() => {
                history.push('/logout/callback');
            }}
            onLoginCallback={async () => {
                const query = queryString.parse(location.search);

                if (!query.jwt) {
                    return;
                }

                return query.jwt;
            }}
            onLogoutCallback={async () => {}}
        >
            {children}
        </AuthProvider>
    );
};

export default MockAuth;
