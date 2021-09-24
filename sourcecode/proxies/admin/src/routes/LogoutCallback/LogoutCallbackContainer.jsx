import React from 'react';

import LogoutCallback from './LogoutCallback';
import { useHistory } from 'react-router-dom';
import authContext from '../../contexts/auth.js';
import request from '../../helpers/request.js';

const LogoutCallbackContainer = ({ email, redirect = true, ...props }) => {
    const history = useHistory();
    const { logout } = React.useContext(authContext);
    const [{ loading, error }, setStatus] = React.useState({
        loading: true,
        error: false,
    });

    React.useEffect(() => {
        request(`/auth/v1/logout`, 'GET', { json: false })
            .then(() => {
                setStatus({
                    loading: false,
                    error: false,
                });
                logout();
                history.push('/login');
            })
            .catch((error) => {
                setStatus({
                    loading: false,
                    error,
                });
            });
    }, [history, logout]);

    return <LogoutCallback {...props} loading={loading} error={error} />;
};

export default LogoutCallbackContainer;
