import React from 'react';
import { Redirect, Route, Switch } from 'react-router-dom';
import ExternalApplications from './routes/ExternalApplications';

const Index = ({ match }) => {
    return (
        <Switch>
            <Route
                path={`${match.path}/external-applications`}
                component={ExternalApplications}
            />
            <Redirect to={`${match.path}/external-applications`} />
        </Switch>
    );
};

export default Index;
