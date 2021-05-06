import React from 'react';
import useConfig from '../useConfig';
import useRequestWithToken from '../useRequestWithToken';

export const useEdlibResource = () => {
    const request = useRequestWithToken();
    const { edlib } = useConfig();

    return (resourceId) =>
        request(
            edlib(`/resources/v1/resources/${resourceId}/lti-links`),
            'POST'
        );
};

export const withUseResource = (Component) => (props) => {
    const func = useEdlibResource();
    return <Component {...props} useEdlibResource={func} />;
};
