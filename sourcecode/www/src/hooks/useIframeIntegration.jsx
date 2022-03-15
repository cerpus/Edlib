import { useLocation } from 'react-router-dom';
import React from 'react';
import queryString from 'query-string';
import i18n from '../i18n';
import { useConfigurationContext } from '../contexts/Configuration.jsx';

const useIframeIntegration = (requiredParams = []) => {
    const location = useLocation();
    const { isSSR } = useConfigurationContext();

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

    if (isSSR) {
        i18n.changeLanguage(queryParams.language);
    }

    React.useEffect(() => {
        i18n.changeLanguage(queryParams.language);
    }, [queryParams]);

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
