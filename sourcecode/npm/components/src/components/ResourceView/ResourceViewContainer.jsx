import React from 'react';
import ResourceView from './ResourceView';
import useConfig from '../../hooks/useConfig';
import useFetch from '../../hooks/useFetch';
import DefaultFetcher from '../../containers/DefaultFetcher';

const ResourceViewContainer = ({ resourceId }) => {
    const { edlib } = useConfig();

    const getPreviewInfoFetch = useFetch(
        edlib(`/lti/v1/resources/${resourceId}/view`),
        'GET'
    );

    return (
        <DefaultFetcher useFetchData={getPreviewInfoFetch}>
            {({ response: preview }) => <ResourceView preview={preview} />}
        </DefaultFetcher>
    );
};

export default ResourceViewContainer;
