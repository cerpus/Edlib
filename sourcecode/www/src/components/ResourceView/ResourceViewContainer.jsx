import React from 'react';
import ResourceView from './ResourceView';
import useFetch from '../../hooks/useFetch';
import DefaultFetcher from '../../containers/DefaultFetcher';
import appConfig from '../../config/app.js';

const ResourceViewContainer = ({ resourceId }) => {
    const getPreviewInfoFetch = useFetch(
        `${appConfig.apiUrl}/lti/v1/resources/${resourceId}/view`,
        'GET'
    );

    return (
        <DefaultFetcher useFetchData={getPreviewInfoFetch}>
            {({ response: preview }) => <ResourceView preview={preview} />}
        </DefaultFetcher>
    );
};

export default ResourceViewContainer;
