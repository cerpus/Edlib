import React from 'react';
import configContext from '../../contexts/config.js';
import Auth0 from './Auth0.jsx';
import CerpusAuth from './CerpusAuth.jsx';
import MockAuth from './MockAuth.jsx';
import appConfig from '../../config/api.js';

const AuthProviderContainer = ({ children }) => {
    const { authServiceAdapter: adapter } = React.useContext(configContext);

    if (appConfig.showMockLogin) {
        return <MockAuth children={children} />;
    }

    if (adapter === 'cerpusAuth') {
        return <CerpusAuth children={children} />;
    }

    return <Auth0 children={children} />;
};

export default AuthProviderContainer;
