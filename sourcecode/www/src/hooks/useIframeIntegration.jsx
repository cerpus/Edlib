import { useLocation } from 'react-router-dom';
import React from 'react';
import queryString from 'query-string';

const useIframeIntegration = (requiredParams = []) => {
    const location = useLocation();

    const queryParams = React.useMemo(() => {
        return queryString.parse(location.search);
    }, []);

    const invalid = [
        'jwt',
        'nonce',
        'language',
        'configuration',
        ...requiredParams,
    ].some((param) => !queryParams[param]);

    if (invalid) {
        return null;
    }

    return {
        queryParams,
        nonce: queryParams.nonce,
        jwt: queryParams.jwt,
        onAction: (messageType, extras = null) =>
            window.parent.postMessage(
                {
                    audience: 'external',
                    nonce: queryParams.nonce,
                    messageType,
                    extras,
                },
                '*'
            ),
    };
};

export default useIframeIntegration;
