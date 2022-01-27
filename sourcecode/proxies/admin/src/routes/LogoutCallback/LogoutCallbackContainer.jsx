import React from 'react';

import LogoutCallback from './LogoutCallback';
import { useHistory } from 'react-router-dom';
import authContext from '../../contexts/auth.js';

const LogoutCallbackContainer = ({ email, redirect = true, ...props }) => {
    const history = useHistory();
    const { onLogoutCallback } = React.useContext(authContext);
    const [{ loading, error }, setStatus] = React.useState({
        loading: true,
        error: false,
    });

    React.useEffect(() => {
        onLogoutCallback();
    }, [history, onLogoutCallback]);

    return <LogoutCallback {...props} loading={loading} error={error} />;
};

export default LogoutCallbackContainer;
