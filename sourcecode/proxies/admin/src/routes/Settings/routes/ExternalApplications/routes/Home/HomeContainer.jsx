import React from 'react';
import Home from './Home.jsx';
import { useHistory } from 'react-router-dom';
import useFetch from '../../../../../../hooks/useFetch';

const HomeContainer = ({ match }) => {
    const history = useHistory();

    const { response, loading, refetch } = useFetch(
        '/common/applications',
        'GET',
        React.useMemo(() => ({}), [])
    );

    return (
        <Home
            onGoToDetails={(id) => history.push(`${match.path}/${id}`)}
            loading={loading}
            applications={response}
            refetchApplications={refetch}
        />
    );
};

export default HomeContainer;
