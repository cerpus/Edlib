import React from 'react';
import useConfig from '../useConfig';
import useFetchWithToken from '../useFetchWithToken';

export default (requestBody, wait) => {
    const { edlib } = useConfig();

    const options = React.useMemo(
        () => ({
            query: requestBody,
        }),
        [requestBody]
    );

    const { error, loading, response, refetch } = useFetchWithToken(
        edlib('/resources/v2/resources'),
        'GET',
        options,
        wait
    );

    return {
        loading: !error && loading,
        error,
        resources: response && response.data,
        pagination: response && response.pagination,
        refetch,
    };
};
