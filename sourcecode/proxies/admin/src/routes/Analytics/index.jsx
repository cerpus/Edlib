import React from 'react';
import { Redirect, Route, Switch } from 'react-router-dom';
import Dashboard from './routes/Dashboard';

const Index = ({ match }) => {
    return (
        <Switch>
            <Route
                exact
                path={`${match.path}/dashboard`}
                component={Dashboard}
            />
            <Redirect to={`${match.path}/dashboard`} />
        </Switch>
    );
};

export default Index;
