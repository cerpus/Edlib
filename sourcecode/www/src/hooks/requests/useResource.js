import React from 'react';
import appConfig from '../../config/app';
import useRequestWithToken from '../useRequestWithToken';

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
