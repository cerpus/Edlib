import React from 'react';
import {
    ResourceViewer,
    EdlibComponentsProvider,
} from '@cerpus/edlib-components';
import appConfig from '../../../../../config/app.js';
import { Box, Container } from '@material-ui/core';

const ViewResourceContainer = ({ match }) => {
    return (
        <EdlibComponentsProvider
            edlibUrl={appConfig.apiUrl}
            getJwt={() => null}
        >
            <Container>
                <Box padding={1}>
                    <ResourceViewer resourceId={match.params.resourceId}>
                        {match.params.resourceId}
                    </ResourceViewer>
                </Box>
            </Container>
        </EdlibComponentsProvider>
    );
};

export default ViewResourceContainer;
