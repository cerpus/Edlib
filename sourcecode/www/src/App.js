import React from 'react';
import { BrowserRouter } from 'react-router-dom';
import { ThemeProvider } from '@cerpus/ui';
import Routes from './routes/index.js';

function App() {
    return (
        <ThemeProvider>
            <BrowserRouter>
                <Routes />
            </BrowserRouter>
        </ThemeProvider>
    );
}

export default App;
