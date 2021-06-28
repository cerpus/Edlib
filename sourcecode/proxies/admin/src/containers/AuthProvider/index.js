import React from 'react';
import appConfig from '../../config/api.js';
import MockProvider from './MockProvider.jsx';
import AuthProvider from './AuthProvider.jsx';

const Index = (props) => {
    if (appConfig.showMockLogin) {
        return <MockProvider {...props} />;
    }

    return <AuthProvider {...props} />;
};

export default Index;
