import React from 'react';
import { BrowserRouter } from 'react-router-dom';
import { ThemeProvider, themes } from '@cerpus/ui';
import Routes from './routes';
import AuthProvider from './containers/AuthProvider';
import ConfigProvider from './containers/ConfigProvider.jsx';

function App() {
    return (
        <ThemeProvider materialUITheme={themes.edlib}>
            <BrowserRouter basename={process.env.PUBLIC_URL}>
                <ConfigProvider>
                    <AuthProvider>
                        <Routes />
                    </AuthProvider>
                </ConfigProvider>
            </BrowserRouter>
        </ThemeProvider>
    );
}

export default App;
