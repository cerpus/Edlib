import React from 'react';

export default React.createContext({
    authServiceAdapter: 'auth0',
    authServiceSettings: null,
    loginRedirectUrl: `${window.location.origin}/admin/login/callback`,
    logoutRedirectUrl: `${window.location.origin}/admin/logout/callback`,
});
