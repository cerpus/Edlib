import React from 'react';
import debug from 'debug';
import request from '../helpers/request';
import { isTokenExpired } from '../helpers/token.js';

const log = debug('edlib-components:useToken');
const checkFrequency = 5 * 1000; //milliseconds
const tokenExpiryMargin = 2 * 60; //seconds

export default (getJwt, edlibUrl) => {
    const [jwt, setJwt] = React.useState(null);
    const [jwtLoading, setJwtLoading] = React.useState(null);
    const [jwtError, setJwtError] = React.useState(null);
    const [retry, setRetry] = React.useState(null);
    const prevCountRef = React.useRef(null);

    React.useEffect(() => {
        if (!prevCountRef) {
            setJwt(null);
            setJwtError(null);
        }

        prevCountRef.current = getJwt;
    }, [getJwt]);

    const updateToken = React.useCallback(() => {
        if (!getJwt) {
            setJwtLoading(false);
            setJwtError('getJwt is not provided');
            console.error('getJwt is not provided');
            return null;
        }

        setJwtLoading(true);
        const _update = async () => {
            let newInternalToken;

            if (!jwt) {
                const externalToken = await getJwt();
                let getJwtTokenData = externalToken;

                if (typeof externalToken === 'string') {
                    getJwtTokenData = {
                        type: 'external',
                        token: externalToken,
                    };
                }

                if (getJwtTokenData.type === 'internal') {
                    newInternalToken = getJwtTokenData.token;
                } else {
                    const { token: internalToken } = await request(
                        `${edlibUrl}/auth/v1/jwt/convert`,
                        'POST',
                        {
                            body: {
                                externalToken: getJwtTokenData.token,
                            },
                        }
                    );

                    newInternalToken = internalToken;
                }
            } else {
                const { token: internalToken } = await request(
                    `${edlibUrl}/auth/v3/jwt/refresh`,
                    'POST',
                    {
                        headers: {
                            Authorization: `Bearer ${jwt}`,
                        },
                        body: {
                            token: jwt,
                        },
                    }
                );

                newInternalToken = internalToken;
            }

            if (!newInternalToken) {
                setJwtError('jwt was not returned from getJwt function');
                return console.error(
                    'jwt was not returned from getJwt function'
                );
            } else if (isTokenExpired(newInternalToken)) {
                setJwtError('Returned token has expired');
                return console.error('Returned token has expired');
            }

            return setJwt(newInternalToken);
        };
        _update()
            .catch((e) => {
                console.error(e);
                setJwtError('Noe skjedde');
                setRetry(retry ? retry + 1 : 1);
            })
            .finally(() => {
                setJwtLoading(false);
            });
    }, [getJwt, setJwt, setJwtLoading, setJwtError, jwt]);

    const updateTokenIfRequired = React.useCallback(() => {
        log('Check if token must be updated');
        if (jwtLoading) {
            log('Not refreshing token as it is already loading a new.');
            return;
        }

        if (jwtError && retry === null) {
            log(
                'Not refreshing token as an error has occurred and retry is null'
            );
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
    }, [updateToken, jwtLoading, jwtError, jwt]);

    React.useEffect(() => {
        const interval = setInterval(() => {
            updateTokenIfRequired();
        }, checkFrequency);

        return () => clearInterval(interval);
    }, [updateTokenIfRequired]);

    React.useEffect(() => {
        if (retry) {
            setTimeout(() => {
                updateTokenIfRequired();
            }, 5000);
        }
    }, [retry]);

    React.useEffect(() => {
        log('Triggering initial token loading');
        updateTokenIfRequired();
    }, []);

    React.useEffect(() => {
        log('Jwt updated to ', jwt);
    }, [jwt]);

    const getToken = React.useCallback(async () => {
        if (jwt && !isTokenExpired(jwt)) {
            return jwt;
        }

        throw new Error('No valid token is available');
    }, [jwt]);

    return {
        token: jwt,
        loading: jwtLoading,
        error: jwtError,
        getToken,
    };
};
