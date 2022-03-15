import React from 'react';
import useResourceSource from '../useResourceSource';
import useFetchWithToken from '../useFetchWithToken';
import { useConfigurationContext } from '../../contexts/Configuration.jsx';

export default (resource) => {
    const { edlibApi } = useConfigurationContext();

    const {
        error,
        loading,
        response: preview,
    } = useFetchWithToken(
        edlibApi(
            `/lti/v2/resources/${resource.id}/preview?resourceVersionId=${resource.version.id}`
        ),
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
