import React from 'react';
import { Redirect, Route, Switch } from 'react-router-dom';
import MigrateCore from './routes/MigrateCore';
import Resources from './routes/Resources.jsx';

const Index = ({ match }) => {
    return (
        <Switch>
            <Route
                exact
                path={`${match.path}/migrate-core`}
                component={MigrateCore}
            />
            <Route
                exact
                path={`${match.path}/resources`}
                component={Resources}
            />
            <Redirect to={`${match.path}/migrate-core`} />
        </Switch>
    );
};

export default Index;
