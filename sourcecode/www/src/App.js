import React from 'react';
import { BrowserRouter } from 'react-router-dom';
import ThemeSetup from './components/ThemeSetup';
import { RequestCacheProvider } from './contexts/RequestCache';
import Routes from './routes/index.js';

function App() {
    return (
        <ThemeSetup>
            <RequestCacheProvider>
                <BrowserRouter>
                    <Routes />
                </BrowserRouter>
            </RequestCacheProvider>
        </ThemeSetup>
    );
}

export default App;
