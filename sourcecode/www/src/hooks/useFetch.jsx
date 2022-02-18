import React from 'react';
import request from '../helpers/request';
import { useRequestCacheContext } from '../contexts/RequestCache';

export default (url, method, options, wait = false, cache = false) => {
    const { cachedDataWithStatus, setCachedData, ignoreFirstFetch } =
        useRequestCacheContext(url, method, options, cache);

    const [loading, setLoading] = React.useState(cachedDataWithStatus.loading);
    const [error, setError] = React.useState(cachedDataWithStatus.error);
    const [response, setResponse] = React.useState(
        cachedDataWithStatus.response
    );
    const [fetchId, setFetchId] = React.useState(1);

    React.useEffect(() => {
        if (ignoreFirstFetch && fetchId === 1) {
            return;
        }

        setLoading(true);

        if (wait) {
            return;
        }

        const abortController = new AbortController();
        const newOptions = { ...options, signal: abortController.signal };

        request(url, method, newOptions)
            .then((r) => {
                setCachedData(r);
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
    }, [url, method, options, wait, fetchId, ignoreFirstFetch]);

    const refetch = React.useCallback(
        () => setFetchId(fetchId + 1),
        [setFetchId, fetchId]
    );

    return {
        loading,
        error,
        response,
        refetch,
    };
};
