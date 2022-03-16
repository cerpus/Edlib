import React from 'react';
import { Route, Switch } from 'react-router-dom';
import StandaloneComponents from './routes/StandaloneComponents';
import { Helmet } from 'react-helmet';

const Index = () => {
    return (
        <>
            <Helmet>
                <title>Edlib</title>
            </Helmet>
            <Switch>
                {/* "/s" paths are all standalone routes which can be integrated into iframes */}
                <Route path="/s" component={StandaloneComponents} />
            </Switch>
        </>
    );
};

export default Index;
