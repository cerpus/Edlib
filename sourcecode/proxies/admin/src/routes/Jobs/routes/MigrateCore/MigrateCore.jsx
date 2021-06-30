import React from 'react';
import { Container, Grid } from '@material-ui/core';
import Job from '../../../../components/Job';

const MigrateCore = () => {
    return (
        <Container>
            <Grid container>
                <Grid item md={12}>
                    <Job
                        name="1. Flytt url fra core til edlib2"
                        startUrl="/url/v1/sync-resources"
                        statusUrl={(jobId) => `/url/v1/sync-resources/${jobId}`}
                    />
                </Grid>
                <Grid item md={12}>
                    <Job
                        name="2. Sync eksterne ressurser"
                        startUrl="/resources/v1/jobs/sync-external-resources"
                        statusUrl={(jobId) => `/resources/v1/jobs/${jobId}`}
                        showKillButton
                        resumable
                    />
                </Grid>
                <Grid item md={12}>
                    <Job
                        name="3. Sync core ids"
                        startUrl="/resources/v1/jobs/sync-core-ids"
                        statusUrl={(jobId) => `/resources/v1/jobs/${jobId}`}
                        showKillButton
                        resumable
                    />
                </Grid>
                <Grid item md={12}>
                    <Job
                        name="4. Sync lti"
                        startUrl="/lti/v1/sync-lti"
                        statusUrl={(jobId) => `/lti/v1/sync-lti/${jobId}`}
                    />
                </Grid>
                <Grid item md={12}>
                    <Job
                        name="5. Sync lti usage views with resourceapi"
                        startUrl="/resources/v1/jobs/sync-lti-usage-views"
                        statusUrl={(jobId) => `/resources/v1/jobs/${jobId}`}
                        showKillButton
                    />
                </Grid>
                <Grid item md={12}>
                    <Job
                        name="6. Oppdater elasticsearch index"
                        startUrl="/resources/v1/jobs/refresh-elasticsearch-index"
                        statusUrl={(jobId) => `/resources/v1/jobs/${jobId}`}
                        showKillButton
                    />
                </Grid>
            </Grid>
        </Container>
    );
};

export default MigrateCore;
