import React from 'react';

import LoginCallback from './LoginCallback';
import { useLocation, useHistory } from 'react-router-dom';
import queryString from 'query-string';
import configContext from '../../contexts/config.js';
import authContext from '../../contexts/auth.js';
import request from '../../helpers/request.js';

const LoginCallbackContainer = ({ email, redirect = true, ...props }) => {
    const location = useLocation();
    const history = useHistory();
    const { loginRedirectUrl } = React.useContext(configContext);
    const { login } = React.useContext(authContext);
    const [{ loading, error }, setStatus] = React.useState({
        loading: true,
        error: false,
    });

    React.useEffect(() => {
        const query = queryString.parse(location.search);

        if (!query.code) {
            return;
        }

        request(
            `/auth/v1/login/callback?code=${query.code}&callbackUrl=${loginRedirectUrl}`,
            'GET'
        )
            .then((response) => {
                setStatus({
                    loading: false,
                    error: false,
                });
                login(response);
                history.push('/');
            })
            .catch((error) => {
                setStatus({
                    loading: false,
                    error,
                });
            });
    }, []);

    return <LoginCallback {...props} loading={loading} error={error} />;
};

export default LoginCallbackContainer;
