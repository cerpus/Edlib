import React from 'react';
import useFetch from './useFetch';
import { useEdlibComponentsContext } from '../contexts/EdlibComponents';

export default (url, method, options, forceToken = true, cache = false) => {
    const { tokenControllerData } = useEdlibComponentsContext();

    const { error, loading, response, refetch, setResponse } = useFetch(
        url,
        method,
        options,
        forceToken && !tokenControllerData.ready,
        cache
    );

    const hasError =
        forceToken &&
        ((!tokenControllerData.ready && tokenControllerData.error) || error);

    return {
        loading: !hasError && loading,
        error: hasError,
        response,
        setResponse,
        refetch,
    };
};
