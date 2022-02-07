import React from 'react';
import moment from 'moment';
import Dashboard from './Dashboard.jsx';
import useFetchWithToken from '../../../../hooks/useFetchWithToken.jsx';

const DashboardContainer = () => {
    const { response, loading, error } = useFetchWithToken(
        '/resources/v1/stats/resource-version/views/by-day',
        'GET'
    );

    const from = React.useMemo(() =>
        moment().subtract(7, 'days').startOf('day')
    );

    const to = React.useMemo(() => moment().endOf('day'));

    return (
        <Dashboard
            loading={loading}
            from={from}
            to={to}
            viewsByDay={response && response.data ? response.data : []}
        />
    );
};

export default DashboardContainer;
