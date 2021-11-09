import React from 'react';
import ExportWrapper from '../../components/ExportWrapper';
import ResourceView from '../../components/ResourceView';

const ResourceViewerContainer = ({ resourceId }) => {
    return (
        <ExportWrapper>
            <ResourceView resourceId={resourceId} />
        </ExportWrapper>
    );
};

export default ResourceViewerContainer;
