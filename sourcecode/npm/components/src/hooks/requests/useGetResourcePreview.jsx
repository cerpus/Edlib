import React from 'react';
import useConfig from '../useConfig';
import useResourceSource from '../useResourceSource';
import useFetchWithToken from '../useFetchWithToken';

export default (resource) => {
    const { edlib } = useConfig();

    const { error, loading, response: preview } = useFetchWithToken(
        edlib(`/lti/v2/resources/${resource.id}/preview`),
        'GET'
    );

    const source = useResourceSource(resource, preview);
    const license = resource.license || null;

    const hasError = !!error;

    return {
        loading: !hasError && loading,
        error: hasError,
        source,
        license,
        preview,
    };
};
