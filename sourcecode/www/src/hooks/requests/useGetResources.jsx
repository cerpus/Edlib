import React from 'react';
import useFetchWithToken from '../useFetchWithToken';
import { useConfigurationContext } from '../../contexts/Configuration.jsx';

export default (requestBody) => {
    const { edlibApi } = useConfigurationContext();

    const options = React.useMemo(
        () => ({
            query: requestBody,
        }),
        [requestBody]
    );

    const { error, loading, response, refetch } = useFetchWithToken(
        edlibApi('/resources/v2/resources'),
        'GET',
        options,
        true,
        false
    );

    return {
        loading: !error && loading,
        error,
        resources: response && response.data,
        filterCount: response && response.filterCount,
        pagination: response && response.pagination,
        refetch,
    };
};
