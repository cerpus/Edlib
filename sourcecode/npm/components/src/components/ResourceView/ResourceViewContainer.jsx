import React from 'react';
import ResourceView from './ResourceView.jsx';
import useConfig from '../../hooks/useConfig.js';
import useFetch from '../../hooks/useFetch.jsx';
import DefaultFetcher from '../../containers/DefaultFetcher.jsx';

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
