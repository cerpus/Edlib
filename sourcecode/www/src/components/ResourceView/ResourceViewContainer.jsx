import React from 'react';
import ResourceView from './ResourceView';
import useFetch from '../../hooks/useFetch';
import DefaultFetcher from '../../containers/DefaultFetcher';
import { useConfigurationContext } from '../../contexts/Configuration.jsx';
import { Helmet } from 'react-helmet';

const ResourceViewContainer = ({ resourceId }) => {
    const { edlibApi } = useConfigurationContext();
    const getPreviewInfoFetch = useFetch(
        edlibApi(`/lti/v1/resources/${resourceId}/view`),
        'GET'
    );

    return (
        <>
            <Helmet>
                <title>
                    {getPreviewInfoFetch.response &&
                        getPreviewInfoFetch.response.resourceVersion.title}
                </title>
            </Helmet>
            <DefaultFetcher useFetchData={getPreviewInfoFetch}>
                {({ response: preview }) => <ResourceView preview={preview} />}
            </DefaultFetcher>
        </>
    );
};

export default ResourceViewContainer;
