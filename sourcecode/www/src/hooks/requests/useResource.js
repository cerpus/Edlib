import React from 'react';
import useRequestWithToken from '../useRequestWithToken';
import appConfig from '../../config/app';

export const useEdlibResource = () => {
    const request = useRequestWithToken();

    return (resourceId, resourceVersionId) =>
        request(
            `${appConfig.apiUrl}/lti/v2/resources/${resourceId}/lti-links`,
            'POST',
            {
                body: {
                    resourceVersionId,
                },
            }
        );
};

export const withUseResource = (Component) => (props) => {
    const func = useEdlibResource();
    return <Component {...props} useEdlibResource={func} />;
};
