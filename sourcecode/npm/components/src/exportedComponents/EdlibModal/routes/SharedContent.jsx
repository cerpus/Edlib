import React from 'react';
import { Breadcrumb, BreadcrumbItem } from '@cerpus/ui';
import ResourcePage from '../../../components/ResourcePage/ResourcePage';
import useResourcesFilters from '../../../hooks/useResourcesFilters';
import useTranslation from '../../../hooks/useTranslation';

const SharedContent = () => {
    const { t } = useTranslation();
    const [selectedResource, setSelectedResource] = React.useState(null);
    const filters = useResourcesFilters('sharedContent');

    return (
        <ResourcePage
            selectedResource={selectedResource}
            setSelectedResource={setSelectedResource}
            breadcrumb={
                <Breadcrumb>
                    <BreadcrumbItem active>Edlib</BreadcrumbItem>
                    <BreadcrumbItem to="/my-content" active>
                        {t('Delt innhold')}
                    </BreadcrumbItem>
                </Breadcrumb>
            }
            filters={filters}
        />
    );
};

export default SharedContent;
