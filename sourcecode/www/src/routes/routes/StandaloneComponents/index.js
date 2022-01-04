import React from 'react';
import { Switch, Route } from 'react-router-dom';
import ViewResource from './routes/ViewResource';
import LtiBrowser from './routes/LtiBrowser';

const Index = ({ match }) => (
    <Switch>
        <Route
            exact
            path={`${match.path}/resources/:resourceId`}
            component={ViewResource}
        />
        <Route
            exact
            path={`${match.path}/lti/browser`}
            component={LtiBrowser}
        />
    </Switch>
);

export default Index;
