import React from 'react';
import useResourcesFilters from '../../../../../../hooks/useResourcesFilters';
import ResourcePage from '../../../../../../components/ResourcePage';

const MyContent = () => {
    const [selectedResource, setSelectedResource] = React.useState(null);
    const filters = useResourcesFilters('myContent');

    return (
        <ResourcePage
            selectedResource={selectedResource}
            setSelectedResource={setSelectedResource}
            filters={filters}
            showDeleteButton
        />
    );
};

export default MyContent;
