import React from 'react';
import request from '../helpers/request';

const useFetch = (url, method, options, wait = false) => {
    const [loading, setLoading] = React.useState(true);
    const [error, setError] = React.useState(false);
    const [response, setResponse] = React.useState(null);
    const [fetchId, setFetchId] = React.useState(1);

    React.useEffect(() => {
        setLoading(true);

        if (wait) {
            return;
        }

        const abortController = new AbortController();
        const newOptions = { ...options, signal: abortController.signal };

        request(url, method, newOptions)
            .then((r) => {
                setResponse(r);
                setLoading(false);
                setError(false);
            })
            .catch((e) => {
                if (e.name !== 'AbortError') {
                    setError(e);
                    setLoading(false);
                }
            });

        return () => {
            abortController.abort();
        };
    }, [url, method, options, wait, fetchId]);

    const refetch = React.useCallback(
        () => setFetchId(fetchId + 1),
        [setFetchId, fetchId]
    );

    return {
        loading,
        error,
        response,
        setResponse,
        refetch,
    };
};

export default useFetch;
