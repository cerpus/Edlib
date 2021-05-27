import React from 'react';
import { BrowserRouter } from 'react-router-dom';
import Routes from './routes';
import AuthProvider from './containers/AuthProvider';
import ConfigProvider from './containers/ConfigProvider.jsx';

function App() {
    return (
        <BrowserRouter basename={process.env.PUBLIC_URL}>
            <ConfigProvider>
                <AuthProvider>
                    <Routes />
                </AuthProvider>
            </ConfigProvider>
        </BrowserRouter>
    );
}

export default App;
