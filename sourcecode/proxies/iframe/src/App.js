import React from 'react';
import { BrowserRouter } from 'react-router-dom';
import { ThemeProvider } from '@cerpus/ui';
import Routes from './routes';

function App() {
    return (
        <ThemeProvider>
            <BrowserRouter basename={process.env.PUBLIC_URL}>
                <Routes />
            </BrowserRouter>
        </ThemeProvider>
    );
}

export default App;
