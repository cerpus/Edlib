import React from 'react';

const FetchContext = React.createContext({});

export const addPromiseListToState = async (currentState = {}, promiseList) => {
    return await Promise.all(
        promiseList.map(async (data) => {
            try {
                const response = await data.promise;
                currentState[data.requestId] = {
                    response,
                };
            } catch (e) {
                currentState[data.requestId] = {
                    error: e,
                };
            }
        })
    );
};

export const FetchProvider = ({
    children,
    initialState = {},
    promiseList = null,
    ssrCookies = null,
    ssrAddCookiesFromSetCookie = null,
}) => {
    const [cache, setCache] = React.useState(initialState);
    const liveCache = React.useRef(initialState);

    return (
        <FetchContext.Provider
            value={{
                cache,
                liveCache,
                setCache: (cache) => {
                    liveCache.current = cache;
                    setCache(cache);
                },
                promiseList,
                ssrCookies,
                ssrAddCookiesFromSetCookie,
            }}
        >
            {children}
        </FetchContext.Provider>
    );
};

export const useFetchContext = (url, method, options, shouldCache = true) => {
    const {
        cache,
        liveCache,
        setCache,
        promiseList,
        ssrCookies,
        ssrAddCookiesFromSetCookie,
    } = React.useContext(FetchContext);
    const ssr = React.useMemo(() => !!promiseList, [promiseList]);
    const actualShouldCache = React.useMemo(
        () => ssr || shouldCache,
        [ssr, shouldCache]
    );
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
        () => (cache[requestId] ? cache[requestId] : null),
        [cache, requestId]
    );

    const cachedDataWithStatus = React.useMemo(
        () =>
            cachedData
                ? {
                      loading: false,
                      error: cachedData.error || false,
                      response: cachedData.response || null,
                  }
                : {
                      loading: true,
                      error: false,
                      response: null,
                  },
        [cachedData]
    );

    return {
        requestId,
        ssrOptions: {
            ...options,
            cookies: ssrCookies,
            addCookiesFromSetCookie: ssrAddCookiesFromSetCookie,
        },
        cachedDataWithStatus,
        setCachedData: (response) => {
            setCache({
                ...liveCache.current,
                [requestId]: { response },
            });
        },
        clearCacheEntry: (requestId, ignoreIfCache = true) => {
            if (!ignoreIfCache || !actualShouldCache) {
                setCache({
                    ...liveCache.current,
                    [requestId]: undefined,
                });
            }
        },
        hasData: !!cachedData,
        ssr: !!promiseList,
        addToPromiseList: (promise) =>
            promiseList.push({
                requestId,
                promise,
            }),
    };
};
