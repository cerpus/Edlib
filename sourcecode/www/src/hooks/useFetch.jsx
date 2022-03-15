import React from 'react';
import request from '../helpers/request';
import { useFetchContext } from '../contexts/Fetch.jsx';

export default (url, method, options, wait = false, cache = false) => {
    const {
        cachedDataWithStatus,
        setCachedData,
        clearCacheEntry,
        hasData,
        ssr,
        addToPromiseList,
        ssrOptions,
    } = useFetchContext(url, method, options, cache);

    const [loading, setLoading] = React.useState(cachedDataWithStatus.loading);
    const [error, setError] = React.useState(cachedDataWithStatus.error);
    const [response, setResponse] = React.useState(
        cachedDataWithStatus.response
    );
    const [fetchId, setFetchId] = React.useState(1);

    if (!hasData && !wait && ssr) {
        addToPromiseList(request(url, method, ssrOptions));
    }

    React.useEffect(() => {
        if (hasData) {
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
    }, [url, method, options, wait, fetchId, hasData]);

    const refetch = React.useCallback(
        () => setFetchId(fetchId + 1),
        [setFetchId, fetchId]
    );

    React.useEffect(() => {
        return () => clearCacheEntry();
    }, []);

    return {
        loading,
        error,
        response,
        setResponse,
        refetch,
    };
};
