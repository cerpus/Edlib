import { useCallback, useEffect, useRef, useState } from 'react';
import debug from 'debug';
import request from '../helpers/request';
import { isTokenExpired } from '../helpers/token';

const log = debug('useToken');
const checkFrequency = 5 * 1000; //milliseconds
const tokenExpiryMargin = 2 * 60; //seconds

const useToken = ((getJwt) => {
    const [jwt, setJwt] = useState(null);
    const [jwtLoading, setJwtLoading] = useState(null);
    const [jwtError, setJwtError] = useState(null);
    const [retry, setRetry] = useState(null);
    const prevCountRef = useRef(null);
    const currentId = useRef(1);

    useEffect(() => {
        if (!prevCountRef) {
            setJwt(null);
            setJwtError(null);
        }

        prevCountRef.current = getJwt;
    }, [getJwt]);

    const updateToken = useCallback(() => {
        if (!getJwt) {
            setJwtLoading(false);
            setJwtError('getJwt is not provided');
            console.error('getJwt is not provided');
            return null;
        }

        const id = currentId.current + 1;
        currentId.current = id;
        setJwtLoading(true);

        const _update = async () => {
            let newInternalToken;

            if (!jwt) {
                const externalToken = await getJwt();

                if (!externalToken) {
                    setJwtError('jwt was not returned from getJwt function');
                    return console.error('jwt was not returned from getJwt function');
                }

                let getJwtTokenData = externalToken;

                if (typeof externalToken === 'string') {
                    getJwtTokenData = {
                        type: 'external',
                        token: externalToken
                    };
                }

                if (getJwtTokenData.type === 'internal') {
                    newInternalToken = getJwtTokenData.token;
                } else {
                    const {
                        token: internalToken
                    } = await request('/auth/v1/jwt/convert', 'POST', {
                        body: {
                            externalToken: getJwtTokenData.token
                        }
                    });
                    newInternalToken = internalToken;
                }
            } else {
                const {
                    token: internalToken
                } = await request('/auth/v3/jwt/refresh', 'POST', {
                    headers: {
                        Authorization: `Bearer ${jwt}`
                    },
                    body: {
                        token: jwt
                    }
                });
                newInternalToken = internalToken;
            }

            if (id !== currentId.current) {
                return;
            }

            if (!newInternalToken) {
                setJwtError('Error creating internal JWT token');
                return console.error('Error creating internal JWT token');
            } else if (isTokenExpired(newInternalToken)) {
                setJwtError('Returned token has expired');
                return console.error('Returned token has expired');
            }

            return setJwt(newInternalToken);
        };

        _update().catch(e => {
            if (id !== currentId.current) {
                return;
            }

            console.error(e);
            setJwtError('Noe skjedde');
            setRetry(retry => retry ? retry + 1 : 1);
        }).finally(() => {
            if (id !== currentId.current) {
                return;
            }

            setJwtLoading(false);
        });
    }, [getJwt, setJwt, setJwtLoading, setJwtError, jwt]);

    const updateTokenIfRequired = useCallback(() => {
        log('Check if token must be updated');

        if (jwtLoading) {
            log('Not refreshing token as it is already loading a new');
            return;
        }

        if (jwtError && retry === null) {
            log('Not refreshing token as an error has occurred and retry is null');
        }

        if (jwt) {
            if (!isTokenExpired(jwt, tokenExpiryMargin)) {
                return;
            }

            log('Refreshing token as it has expired');
        } else {
            log("Refreshing token as it doesn't exists");
        }

        updateToken();
    }, [updateToken, jwtLoading, jwtError, jwt, retry]);

    useEffect(() => {
        const interval = setInterval(() => {
            updateTokenIfRequired();
        }, checkFrequency);
        return () => clearInterval(interval);
    }, [updateTokenIfRequired]);

    useEffect(() => {
        if (retry) {
            setTimeout(() => {
                updateTokenIfRequired();
            }, 5000);
        }
    }, [retry, updateTokenIfRequired]);

    useEffect(() => {
        log('Triggering initial token loading');
        updateTokenIfRequired();
    }, [updateTokenIfRequired]);

    useEffect(() => {
        log('Jwt updated to ', jwt);
    }, [jwt]);

    const getToken = useCallback(async () => {
        if (jwt && !isTokenExpired(jwt)) {
            return jwt;
        }

        throw new Error('No valid token is available');
    }, [jwt]);

    const reset = useCallback(async () => {
        setJwt(null);
        setJwtLoading(null);
        setJwtError(null);
        setRetry(null);
        updateToken();
    }, [updateToken]);

    return {
        token: jwt,
        loading: jwtLoading,
        error: jwtError,
        getToken,
        reset
    };
});

export default useToken;
