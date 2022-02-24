import React from 'react';
import { Route, Switch } from 'react-router-dom';
import StandaloneComponents from './routes/StandaloneComponents';

const Index = () => {
    return (
        <Switch>
            {/* "/s" paths are all standalone routes which can be integrated into iframes */}
            <Route path="/s" component={StandaloneComponents} />
        </Switch>
    );
};

export default Index;
