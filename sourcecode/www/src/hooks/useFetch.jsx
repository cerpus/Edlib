import React from 'react';
import request from '../helpers/request';
import { useFetchContext } from '../contexts/Fetch.jsx';
import useUnmount from './useUnmount.js';

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
    const [response, _setResponse] = React.useState(
        cachedDataWithStatus.response
    );
    const setResponse = React.useCallback((response) => {
        _setResponse(response);
        setCachedData(response);
    });

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
                setLoading(false);
                setError(false);
                setResponse(r);
                setCachedData(r);
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
    }, [url, method, options, wait, hasData]);

    const refetch = React.useCallback(() => clearCacheEntry(false), []);

    useUnmount(clearCacheEntry);

    return {
        loading,
        error,
        response,
        setResponse,
        refetch,
    };
};
