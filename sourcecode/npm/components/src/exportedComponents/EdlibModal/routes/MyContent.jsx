import React from 'react';
import ResourcePage from '../../../components/ResourcePage/ResourcePage';
import useResourcesFilters from '../../../hooks/useResourcesFilters';

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
