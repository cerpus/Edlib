import React from 'react';
import ResourcePage from '../../../../../../components/ResourcePage/ResourcePage';
import useResourcesFilters from '../../../../../../hooks/useResourcesFilters';

const SharedContent = () => {
    const filters = useResourcesFilters('sharedContent');

    return <ResourcePage filters={filters} />;
};

export default SharedContent;
