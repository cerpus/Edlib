import React from 'react';
import { Switch, Route, Redirect } from 'react-router-dom';
import Home from './Home.jsx';
import authContext from '../contexts/auth.js';
import LoginCallback from './LoginCallback';
import LogoutCallback from './LogoutCallback';
import Login from './Login.jsx';
import SystemStatuses from './SystemStatuses';
import Settings from './Settings';
import { Box, CircularProgress } from '@material-ui/core';
import Page from '../components/Page';
import Jobs from './Jobs';

const Index = ({ isAuthenticated }) => {
    return (
        <Page>
            {isAuthenticated && (
                <Switch>
                    <Route exact path="/dashboard" component={Home} />
                    <Route
                        exact
                        path="/monitoring/system-status"
                        component={SystemStatuses}
                    />
                    <Route
                        exact
                        path="/logout/callback"
                        component={LogoutCallback}
                    />
                    <Route path="/jobs" component={Jobs} />
                    <Route path="/settings" component={Settings} />
                    <Redirect to="/dashboard" />
                </Switch>
            )}
            {!isAuthenticated && (
                <Switch>
                    <Route exact path="/login" component={Login} />
                    <Route
                        exact
                        path="/login/callback"
                        component={LoginCallback}
                    />
                    <Redirect to="/login" />
                </Switch>
            )}
        </Page>
    );
};

export default () => {
    const { isAuthenticating, isAuthenticated } = React.useContext(authContext);

    if (isAuthenticating) {
        return (
            <Box justifyContent="center" display="flex">
                <CircularProgress />
            </Box>
        );
    }

    return <Index isAuthenticated={isAuthenticated} />;
};
