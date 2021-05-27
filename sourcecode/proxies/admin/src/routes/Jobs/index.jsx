import React from 'react';
import { Redirect, Route, Switch } from 'react-router-dom';
import MigrateCore from './routes/MigrateCore';

const Index = ({ match }) => {
    return (
        <Switch>
            <Route
                exact
                path={`${match.path}/migrate-core`}
                component={MigrateCore}
            />
            <Redirect to={`${match.path}/migrate-core`} />
        </Switch>
    );
};

export default Index;
