import React from 'react';
import Home from './Home.jsx';
import { useHistory } from 'react-router-dom';
import useFetchWithToken from '../../../../../../hooks/useFetchWithToken.jsx';

const HomeContainer = ({ match }) => {
    const history = useHistory();

    const { response, loading, refetch } = useFetchWithToken(
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
