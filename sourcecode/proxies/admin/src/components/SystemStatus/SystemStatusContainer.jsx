import React from 'react';
import useFetch from '../../hooks/useFetch.jsx';
import SystemStatus from './SystemStatus.jsx';

const SystemStatusContainer = ({ name, endpoint }) => {
    const { response, loading, error } = useFetch(endpoint, 'GET');

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
