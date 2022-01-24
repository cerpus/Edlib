import React from 'react';
import { Redirect, Route, Switch } from 'react-router-dom';
import Resources from './routes/Resources.jsx';
import AuthMigration from './routes/AuthMigration';
import AuthMigrationExecute from './routes/AuthMigrationExecute';

const Index = ({ match }) => {
    return (
        <Switch>
            <Route
                exact
                path={`${match.path}/resources`}
                component={Resources}
            />
            <Route
                exact
                path={`${match.path}/auth-migration`}
                component={AuthMigration}
            />
            <Route
                exact
                path={`${match.path}/auth-migration/:id`}
                component={AuthMigrationExecute}
            />
            <Redirect to={`${match.path}/resources`} />
        </Switch>
    );
};

export default Index;
