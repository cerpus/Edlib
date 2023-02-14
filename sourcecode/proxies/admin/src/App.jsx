import React from 'react';
import { BrowserRouter } from 'react-router-dom';
import Routes from './routes';
import AuthProvider from './containers/AuthProvider';
import ConfigProvider from './containers/ConfigProvider.jsx';
import TokenContext from './contexts/token.js';
import { createTheme, ThemeProvider } from '@material-ui/core';

const theme = createTheme({
    palette: {
        primary: {
            main: '#2096F3',
            dark: '#1170BA'
        },
        secondary: {
            main: '#82E066',
            dark: '#1D7105'
        }
    },
    typography: {
        htmlFontSize: 16
    },
});

function App() {
    return (
        <ThemeProvider theme={theme}>
            <BrowserRouter basename={process.env.PUBLIC_URL}>
                <ConfigProvider>
                    <TokenContext>
                        <AuthProvider>
                            <Routes />
                        </AuthProvider>
                    </TokenContext>
                </ConfigProvider>
            </BrowserRouter>
        </ThemeProvider>
    );
}

export default App;
