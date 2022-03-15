import React from 'react';
import ThemeSetup from './components/ThemeSetup';
import Routes from './routes/index.js';
import './index.css';

const App = () => {
    return (
        <ThemeSetup>
            <Routes />
        </ThemeSetup>
    );
};

export default App;
