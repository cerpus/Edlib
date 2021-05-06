import React from 'react';
import useFetch from './useFetch';
import { useEdlibComponentsContext } from '../contexts/EdlibComponents';

export default (url, method, options, wait = false, forceToken = true) => {
    const { jwt } = useEdlibComponentsContext();
    const [hasChanged, setHasChanged] = React.useState(false);
    const previousJwt = React.useRef(jwt.value);

    React.useEffect(() => {
        setHasChanged(true);
    }, [url, method, options]);

    React.useEffect(() => {
        if (hasChanged) {
            setHasChanged(false);
        }
    }, [hasChanged]);

    React.useEffect(() => {
        if (!previousJwt.current) {
            setHasChanged(true);
        }
    }, [jwt.value]);

    const tempJwt = React.useMemo(() => {
        if (jwt.value && (hasChanged || !previousJwt.current)) {
            previousJwt.current = jwt.value;
        }

        return previousJwt.current;
    }, [hasChanged, jwt.value]);

    const requestOptions = React.useMemo(() => {
        let headers = {};

        if (tempJwt) {
            headers.Authorization = `Bearer ${tempJwt}`;
        }

        if (options && options.headers) {
            headers = { ...headers, ...options.headers };
        }
        return {
            ...options,
            headers,
        };
    }, [options, tempJwt]);

    const { error, loading, response, refetch } = useFetch(
        url,
        method,
        requestOptions,
        (forceToken && !tempJwt) || wait
    );

    const hasError = forceToken && ((!jwt.value && jwt.error) || error);

    return {
        loading: !hasError && loading,
        error: hasError,
        response,
        refetch,
    };
};
