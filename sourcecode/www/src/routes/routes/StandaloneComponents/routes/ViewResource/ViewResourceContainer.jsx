import React from 'react';
import { EdlibComponentsProvider } from '../../../../../contexts/EdlibComponents';
import ResourceView from '../../../../../components/ResourceView';

const ViewResourceContainer = ({ match }) => {
    return (
        <EdlibComponentsProvider>
            <ResourceView resourceId={match.params.resourceId} />
        </EdlibComponentsProvider>
    );
};

export default ViewResourceContainer;
