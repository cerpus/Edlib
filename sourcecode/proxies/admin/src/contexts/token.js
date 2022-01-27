import React from 'react';
import { useToken } from '@cerpus/edlib-components';
import store from 'store';
import storageKeys from '../constants/storageKeys.js';
import apiConfig from '../config/api.js';

const tokenContext = React.createContext({});
export const useTokenContext = () => React.useContext(tokenContext);

const ContextWithToken = ({
    externalToken,
    updateExternalToken,
    removeExternalToken,
    children,
}) => {
    const getJwt = React.useCallback(async () => {
        return externalToken;
    }, [externalToken]);

    const { token, loading, error, getToken } = useToken(getJwt, apiConfig.url);

    React.useEffect(() => {
        if (error && !loading) {
            removeExternalToken();
        }
    }, [error, loading]);

    return (
        <tokenContext.Provider
            value={{
                jwt: {
                    value: token,
                    loading: !error && (!token || loading),
                    error,
                    getToken,
                },
                updateExternalToken,
                removeExternalToken,
            }}
        >
            {children}
        </tokenContext.Provider>
    );
};

const ContextWithoutToken = ({
    updateExternalToken,
    removeExternalToken,
    children,
}) => {
    return (
        <tokenContext.Provider
            value={{
                jwt: {
                    value: null,
                    loading: false,
                    error: false,
                    getToken: async () => null,
                    reset: () => {},
                },
                updateExternalToken,
                removeExternalToken,
            }}
        >
            {children}
        </tokenContext.Provider>
    );
};

const TokenContext = ({ children }) => {
    const [externalToken, setExternalToken] = React.useState(() =>
        store.get(storageKeys.EXTERNAL_TOKEN)
    );

    if (externalToken) {
        return (
            <ContextWithToken
                removeExternalToken={() => {
                    store.remove(storageKeys.EXTERNAL_TOKEN);
                    setExternalToken(null);
                }}
                externalToken={externalToken}
                updateExternalToken={(token) => {
                    store.set(storageKeys.EXTERNAL_TOKEN, token);
                    setExternalToken(token);
                }}
                children={children}
            />
        );
    }

    return (
        <ContextWithoutToken
            removeExternalToken={() => {
                store.remove(storageKeys.EXTERNAL_TOKEN);
                setExternalToken(null);
            }}
            externalToken={externalToken}
            updateExternalToken={(token) => {
                store.set(storageKeys.EXTERNAL_TOKEN, token);
                setExternalToken(token);
            }}
            children={children}
        />
    );
};

export default TokenContext;
