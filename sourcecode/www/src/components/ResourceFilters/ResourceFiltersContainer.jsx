import React from 'react';
import useFetchWithToken from '../../hooks/useFetchWithToken.jsx';
import { CircularProgress } from '@mui/material';
import ResourceFilters from './ResourceFilters.jsx';
import { useConfigurationContext } from '../../contexts/Configuration.jsx';

const ResourceFiltersContainer = (props) => {
    const { edlibApi } = useConfigurationContext();

    const { loading: loadingContentTypes, response: contentTypeResponse } =
        useFetchWithToken(
            edlibApi(`/resources/v2/content-types/contentauthor`),
            'GET',
            React.useMemo(() => ({}), []),
            true,
            true
        );
    const { loading: loadingLicenses, response: licenseResponse } =
        useFetchWithToken(edlibApi(`/resources/v1/filters/licenses`));

    const {
        loading: loadingSavedFilters,
        response: savedFilterResponse,
        setResponse: setResponseSavedFilter,
    } = useFetchWithToken(edlibApi(`/common/saved-filters`));

    if (loadingContentTypes || loadingLicenses || loadingSavedFilters) {
        return <CircularProgress />;
    }

    return (
        <ResourceFilters
            contentTypeData={
                contentTypeResponse ? contentTypeResponse.data : []
            }
            licenseData={licenseResponse ? licenseResponse : []}
            savedFilterData={savedFilterResponse}
            updateSavedFilter={(data, remove = false) => {
                let values;
                if (!remove) {
                    const savedFilter = data;
                    values = Object.values(
                        savedFilterResponse.reduce(
                            (byId, _s) => ({
                                ...byId,
                                [_s.id]: byId[_s.id] || _s,
                            }),
                            {
                                [savedFilter.id]: savedFilter,
                            }
                        )
                    );
                } else {
                    const removedId = data;
                    values = savedFilterResponse.filter(
                        (_s) => _s.id !== removedId
                    );
                }

                setResponseSavedFilter(values);
            }}
            {...props}
        />
    );
};

export default ResourceFiltersContainer;
