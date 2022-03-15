import React from 'react';
import useRequestWithToken from '../useRequestWithToken';
import { useConfigurationContext } from '../../contexts/Configuration.jsx';

export const useEdlibResource = () => {
    const request = useRequestWithToken();
    const { edlibApi } = useConfigurationContext();

    return (resourceId, resourceVersionId) =>
        request(edlibApi(`/lti/v2/resources/${resourceId}/lti-links`), 'POST', {
            body: {
                resourceVersionId,
            },
        });
};

export const withUseResource = (Component) => (props) => {
    const func = useEdlibResource();
    return <Component {...props} useEdlibResource={func} />;
};
