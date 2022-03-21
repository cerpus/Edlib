import React from 'react';

const RequestCacheContext = React.createContext({});

export const RequestCacheProvider = ({ children }) => {
    const [cache, setCache] = React.useState({});
    return (
        <RequestCacheContext.Provider
            value={{
                cache,
                setCache,
            }}
        >
            {children}
        </RequestCacheContext.Provider>
    );
};

export const useRequestCacheContext = (
    url,
    method,
    options,
    shouldCache = true
) => {
    const { cache, setCache } = React.useContext(RequestCacheContext);
    const requestId = React.useMemo(
        () =>
            JSON.stringify({
                url,
                method,
                options,
            }),
        [url, method, options]
    );

    const cachedData = React.useMemo(
        () => (shouldCache && cache[requestId] ? cache[requestId] : null),
        [shouldCache, cache, requestId]
    );

    const cachedDataWithStatus = React.useMemo(
        () =>
            cachedData
                ? {
                      loading: false,
                      error: false,
                      response: cachedData.response,
                  }
                : {
                      loading: true,
                      error: false,
                      response: null,
                  },
        [cachedData]
    );

    return {
        cachedDataWithStatus,
        setCachedData: (response) => {
            if (shouldCache) {
                setCache({
                    ...cache,
                    [requestId]: { response },
                });
            }
        },
        ignoreFirstFetch: shouldCache && cachedData,
    };
};
