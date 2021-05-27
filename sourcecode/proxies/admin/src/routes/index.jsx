import React from 'react';
import { Switch, Route, Redirect } from 'react-router-dom';
import Home from './Home.jsx';
import Header from '../components/Header.jsx';
import authContext from '../contexts/auth.js';
import LoginCallback from './LoginCallback';
import LogoutCallback from './LogoutCallback';
import { Spinner } from 'reactstrap';
import Login from './Login.jsx';
import SystemStatuses from './SystemStatuses';

const Index = ({ isAuthenticated }) => {
    return (
        <>
            <Header />
            {isAuthenticated && (
                <Switch>
                    <Route exact path="/" component={Home} />
                    <Route
                        exact
                        path="/system-status"
                        component={SystemStatuses}
                    />
                    <Route
                        exact
                        path="/logout/callback"
                        component={LogoutCallback}
                    />
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
        </>
    );
};

export default () => {
    const { isAuthenticating, isAuthenticated } = React.useContext(authContext);

    if (isAuthenticating) {
        return (
            <div className="d-flex justify-content-center align-content-center ml-3">
                <Spinner />
            </div>
        );
    }

    return <Index isAuthenticated={isAuthenticated} />;
};
