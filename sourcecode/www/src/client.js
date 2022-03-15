import App from './App';
import { BrowserRouter } from 'react-router-dom';
import React from 'react';
import { hydrate } from 'react-dom';
import { CacheProvider } from '@emotion/react';
import { createEmotionCache, muiTheme } from './muiSetup.js';
import { ThemeProvider } from '@mui/material/styles';
import { FetchProvider } from './contexts/Fetch.jsx';
import { ConfigurationProvider } from './contexts/Configuration.jsx';

const emotionCache = createEmotionCache();

hydrate(
    <CacheProvider value={emotionCache}>
        <ThemeProvider theme={muiTheme}>
            <BrowserRouter>
                <FetchProvider initialState={window.__FETCH_STATE__ || {}}>
                    {/* eslint-disable-next-line no-restricted-globals*/}
                    <ConfigurationProvider
                        apiUrl={(
                            location.protocol +
                            '//' +
                            location.host
                        ).replace('www', 'api')}
                        wwwUrl={location.protocol + '//' + location.host}
                    >
                        <App />
                    </ConfigurationProvider>
                </FetchProvider>
            </BrowserRouter>
        </ThemeProvider>
    </CacheProvider>,
    document.getElementById('root')
);

if (module.hot) {
    module.hot.accept();
}
