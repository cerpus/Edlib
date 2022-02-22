import React from 'react';
import { EdlibComponentsProvider } from '../../../../../contexts/EdlibComponents';
import appConfig from '../../../../../config/app.js';
import { Box, Container } from '@material-ui/core';
import ResourceView from '../../../../../components/ResourceView';

const ViewResourceContainer = ({ match }) => {
    return (
        <EdlibComponentsProvider
            edlibUrl={appConfig.apiUrl}
            getJwt={() => null}
        >
            <Container>
                <Box padding={1}>
                    <ResourceView resourceId={match.params.resourceId} />
                </Box>
            </Container>
        </EdlibComponentsProvider>
    );
};

export default ViewResourceContainer;
