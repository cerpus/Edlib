import React from 'react';
import { Container, Grid } from '@material-ui/core';
import Job from '../../../../components/Job';

const MigrateCore = () => {
    return (
        <Container>
            <Grid container>
                <Grid item md={12}>
                    <Job
                        name="Flytt url fra core til edlib2"
                        startUrl="/url/v1/sync-resources"
                        statusUrl={(jobId) => `/url/v1/sync-resources/${jobId}`}
                    />
                </Grid>
                <Grid item md={12}>
                    <Job
                        name="Sync ressurser"
                        startUrl="/resources/v1/jobs/migrate-old-data"
                        statusUrl={(jobId) => `/resources/v1/jobs/${jobId}`}
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
