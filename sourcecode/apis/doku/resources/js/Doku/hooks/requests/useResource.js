import React from 'react';
import useConfig from '../useConfig';
import useRequestWithToken from '../useRequestWithToken';

export const useEdlibResource = () => {
    const request = useRequestWithToken();
    const { edlib } = useConfig();

    return (resourceId, resourceVersionId) =>
        request(edlib(`/lti/v2/resources/${resourceId}/lti-links`), 'POST', {
            body: {
                resourceVersionId,
            },
        });
};

export const withUseResource = (Component) => (props) => {
    const func = useEdlibResource();
    return <Component {...props} useEdlibResource={func} />;
};
