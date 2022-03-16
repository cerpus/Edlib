import React from 'react';
import debug from 'debug';
import request from '../helpers/request';
import { useConfigurationContext } from '../contexts/Configuration.jsx';
import useFetch from './useFetch.jsx';

const log = debug('edlib-components:useToken');

export default (externalJwt) => {
    const { edlibApi } = useConfigurationContext();
    const [expiresAt, setExpiresAt] = React.useState(null);
    const [jwtError, setJwtError] = React.useState(null);
    const [currentToken, setCurrentToken] = React.useState(null);

    const initialConvertingRequest = useFetch(
        edlibApi(`/auth/v1/jwt/convert`),
        'POST',
        React.useMemo(
            () => ({
                body: {
                    externalToken: externalJwt && externalJwt.token,
                },
            }),
            [externalJwt]
        ),
        !externalJwt
    );

    React.useEffect(() => {
        if (!expiresAt && initialConvertingRequest.response) {
            setExpiresAt(initialConvertingRequest.response.expiresAt);
        }
        if (!currentToken && initialConvertingRequest.response) {
            setCurrentToken(initialConvertingRequest.response.token);
        }
    }, [initialConvertingRequest]);

    React.useEffect(() => {
        if (!expiresAt) {
            return;
        }

        const marginInSeconds = 5 * 60; // 5 minutes
        const expiresInSeconds =
            expiresAt - Math.floor(Date.now() / 1000) - marginInSeconds;

        log(`Token expires in ${expiresInSeconds} seconds`);

        const getToken = (depth = 1) => {
            request(edlibApi(`/auth/v3/jwt/refresh`), 'POST', {})
                .then(({ expiresAt: newExpiresAt, token }) => {
                    setExpiresAt(newExpiresAt);
                    setCurrentToken(token);
                })
                .catch((e) => {
                    log(e);

                    if (depth >= 5) {
                        setJwtError(e);
                        return;
                    }

                    setTimeout(() => getToken(depth + 1), 1000);
                });
        };

        const timeout = setTimeout(() => {
            getToken();
        }, expiresInSeconds * 1000);

        return () => clearTimeout(timeout);
    }, [expiresAt]);

    return {
        loading: initialConvertingRequest.loading,
        error: externalJwt
            ? initialConvertingRequest.error || jwtError
            : 'missing externalToken',
        ready: !!(externalJwt && initialConvertingRequest.response),
        reset: () => {},
        currentToken,
    };
};
