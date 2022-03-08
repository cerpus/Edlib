import React from 'react';
import { Container, Grid } from '@material-ui/core';
import Job from '../../../components/Job/JobContainer.jsx';

const Resources = () => {
    return (
        <Container>
            <Grid container>
                <Grid item md={12}>
                    <Job
                        name="Update resourceapi data from external resource databases"
                        startUrl="/resources/v1/jobs/sync-external-resources"
                        statusUrl={(jobId) => `/resources/v1/jobs/${jobId}`}
                        showKillButton
                    />
                </Grid>
                <Grid item md={12}>
                    <Job
                        name="Oppdater elasticsearch index"
                        startUrl="/resources/v1/jobs/refresh-elasticsearch-index"
                        statusUrl={(jobId) => `/resources/v1/jobs/${jobId}`}
                        showKillButton
                    />
                </Grid>
            </Grid>
        </Container>
    );
};

export default Resources;
