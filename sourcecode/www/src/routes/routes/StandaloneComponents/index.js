import React from 'react';
import { Switch, Route } from 'react-router-dom';
import ViewResource from './routes/ViewResource';

const Index = ({ match }) => (
    <Switch>
        <Route
            exact
            path={`${match.path}/resources/:resourceId`}
            component={ViewResource}
        />
    </Switch>
);

export default Index;
