import React from 'react';
import useFetchWithToken from '../../hooks/useFetchWithToken.jsx';
import useConfig from '../../hooks/useConfig.js';
import { CircularProgress } from '@material-ui/core';
import ResourceFilters from './ResourceFilters.jsx';

const ResourceFiltersContainer = (props) => {
    const { edlib } = useConfig();

    const { loading: loadingContentTypes, response: contentTypeResponse } =
        useFetchWithToken(
            edlib(`/resources/v2/content-types/contentauthor`),
            'GET',
            React.useMemo(() => ({}), []),
            false,
            true,
            true
        );
    const { loading: loadingLicenses, response: licenseResponse } =
        useFetchWithToken(edlib(`/resources/v1/filters/licenses`));

    const {
        loading: loadingSavedFilters,
        response: savedFilterResponse,
        setResponse: setResponseSavedFilter,
    } = useFetchWithToken(edlib(`/common/saved-filters`));

    if (loadingContentTypes || loadingLicenses || loadingSavedFilters) {
        return <CircularProgress />;
    }

    return (
        <ResourceFilters
            contentTypeData={contentTypeResponse.data}
            licenseData={licenseResponse}
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
