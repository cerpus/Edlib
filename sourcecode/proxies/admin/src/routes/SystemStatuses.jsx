import React from 'react';
import SystemStatus from '../components/SystemStatus';
import { Container, Grid } from '@material-ui/core';

const statuses = [
    {
        name: 'EdLibApi - Auth',
        endpoint: '/auth/v1/system-status',
    },
    {
        name: 'EdLibApi - Lti',
        endpoint: '/lti/v1/system-status',
    },
    {
        name: 'EdLibApi - Resources',
        endpoint: '/resources/v1/system-status',
    },
    {
        name: 'EdLibApi - Recommendations',
        endpoint: '/recommendations/v1/system-status',
    },
    {
        name: 'DokuAPI',
        endpoint: '/dokus/dokuapi-system-status',
    },
];

const SystemStatuses = () => {
    return (
        <Container>
            <Grid container>
                <Grid item>
                    {statuses.map((statusInfo, index) => (
                        <SystemStatus
                            key={index}
                            name={statusInfo.name}
                            endpoint={statusInfo.endpoint}
                        />
                    ))}
                </Grid>
            </Grid>
        </Container>
    );
};

export default SystemStatuses;
