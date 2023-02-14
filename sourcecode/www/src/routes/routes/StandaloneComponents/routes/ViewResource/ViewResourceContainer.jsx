import React from 'react';
import { EdlibComponentsProvider } from '../../../../../contexts/EdlibComponents';
import ResourceView from '../../../../../components/ResourceView';
import Container from '@mui/material/Container';

const ViewResourceContainer = ({ match }) => {
    return (
        <EdlibComponentsProvider>
            <Container disableGutters>
                <ResourceView resourceId={match.params.resourceId} />
            </Container>
        </EdlibComponentsProvider>
    );
};

export default ViewResourceContainer;
