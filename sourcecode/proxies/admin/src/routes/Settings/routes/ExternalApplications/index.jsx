import React from 'react';
import { Redirect, Route, Switch } from 'react-router-dom';
import Home from './routes/Home';
import ExternalApplicationDetails from './routes/ExternalApplicationDetails';

const Index = ({ match }) => {
    return (
        <Switch>
            <Route exact path={match.path} component={Home} />
            <Route
                exact
                path={`${match.path}/:id`}
                component={ExternalApplicationDetails}
            />
            <Redirect to={match.path} />
        </Switch>
    );
};

export default Index;
