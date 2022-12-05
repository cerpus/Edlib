import React from 'react';
import { EdlibComponentsProvider } from '../../../../../contexts/EdlibComponents';
import { Box, Container } from '@mui/material';
import ResourceView from '../../../../../components/ResourceView';

const ViewResourceContainer = ({ match }) => {
    return (
        <EdlibComponentsProvider>
            <Container disableGutters>
                <Box padding={1}>
                    <ResourceView resourceId={match.params.resourceId} />
                </Box>
            </Container>
        </EdlibComponentsProvider>
    );
};

export default ViewResourceContainer;
