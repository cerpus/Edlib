import React from 'react';
import SystemStatus from './SystemStatus.jsx';
import useFetchWithToken from '../../hooks/useFetchWithToken.jsx';

const SystemStatusContainer = ({ name, endpoint }) => {
    const { response, loading, error } = useFetchWithToken(endpoint, 'GET');

    return (
        <SystemStatus
            loading={loading}
            error={error}
            data={response}
            name={name}
        />
    );
};

export default SystemStatusContainer;
