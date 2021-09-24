import React from 'react';
import request from '../helpers/request';

const useFetch = (url, method, options, wait = false) => {
    const [loading, setLoading] = React.useState(true);
    const [error, setError] = React.useState(false);
    const [response, setResponse] = React.useState(null);
    const [inc, setInc] = React.useState(0);

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
                setError(e);
                setLoading(false);
            });

        return () => {
            abortController.abort();
        };
    }, [url, method, options, wait, inc]);

    return {
        loading,
        error,
        response,
        setResponse,
        refetch: () => setInc(inc + 1),
    };
};

export default useFetch;
