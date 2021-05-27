import React from 'react';

export default React.createContext({
    authUrl: 'http://auth:8102',
    authClientId: 'b629aec5-58fb-42df-b58e-753affe8f868',
    loginRedirectUrl: `${window.location.origin}/admin/login/callback`,
    logoutRedirectUrl: `${window.location.origin}/admin/logout/callback`,
});
