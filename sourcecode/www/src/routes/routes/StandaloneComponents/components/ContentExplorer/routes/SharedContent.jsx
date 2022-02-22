import React from 'react';
import ResourcePage from '../../../../../../components/ResourcePage/ResourcePage';
import useResourcesFilters from '../../../../../../hooks/useResourcesFilters';

const SharedContent = () => {
    const [selectedResource, setSelectedResource] = React.useState(null);
    const filters = useResourcesFilters('sharedContent');

    return (
        <ResourcePage
            selectedResource={selectedResource}
            setSelectedResource={setSelectedResource}
            filters={filters}
        />
    );
};

export default SharedContent;
