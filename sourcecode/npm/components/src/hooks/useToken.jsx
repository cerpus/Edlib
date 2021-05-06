import React from 'react';
import moment from 'moment';
import debug from 'debug';

const log = debug('edlib-components:useToken');
const checkFrequency = 5 * 1000; //milliseconds
const tokenExpiryMargin = 2 * 60; //seconds

function parseJwt(token) {
    const base64Url = token.split('.')[1];
    const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    const jsonPayload = decodeURIComponent(
        atob(base64)
            .split('')
            .map(function (c) {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            })
            .join('')
    );

    return JSON.parse(jsonPayload);
}

const isTokenExpired = (token, marginSec = 0) => {
    const payload = parseJwt(token);

    return moment
        .unix(payload.exp)
        .isSameOrBefore(moment().add(marginSec, 'seconds'));
};

export default (getJwt) => {
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
        getJwt()
            .then((tempJwt) => {
                if (!tempJwt) {
                    setJwtError('jwt was not returned from getJwt function');
                    console.error('jwt was not returned from getJwt function');
                } else if (isTokenExpired(tempJwt)) {
                    setJwtError('Returned token has expired');
                    console.error('Returned token has expired');
                } else {
                    setJwt(tempJwt);
                }
            })
            .catch((e) => {
                console.error(e);
                setJwtError('Noe skjedde');
                setRetry(retry ? retry + 1 : 1);
            })
            .finally(() => {
                setJwtLoading(false);
            });
    }, [getJwt, setJwt, setJwtLoading, setJwtError]);

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
