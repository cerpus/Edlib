import React from 'react';
import request from '../helpers/request';

export default (url, method, options, wait = false) => {
    const [loading, setLoading] = React.useState(true);
    const [error, setError] = React.useState(false);
    const [response, setResponse] = React.useState(null);

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
    }, [url, method, options, wait]);

    return {
        loading,
        error,
        response,
        setResponse,
    };
};
