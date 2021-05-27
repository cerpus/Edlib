import React from 'react';
import { Container, Grid } from '@material-ui/core';
import Job from './Job';

const MigrateCore = () => {
    return (
        <Container>
            <Grid container>
                <Grid item md={12}>
                    <Job
                        name="Sync ressurser"
                        startUrl="/resources/v1/sync-resources"
                        statusUrl={(jobId) =>
                            `/resources/v1/sync-resources/${jobId}`
                        }
                    />
                </Grid>
                <Grid item md={12}>
                    <Job
                        name="Sync lti"
                        startUrl="/lti/v1/sync-lti"
                        statusUrl={(jobId) => `/lti/v1/sync-lti/${jobId}`}
                    />
                </Grid>
            </Grid>
        </Container>
    );
};

export default MigrateCore;
