import React from 'react';

export default React.createContext({
    isAuthenticated: false,
    isAuthenticating: true,
    user: null,
});
