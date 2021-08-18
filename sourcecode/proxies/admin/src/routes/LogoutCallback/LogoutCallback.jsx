import React from 'react';
import {
    Box,
    CircularProgress,
    Container,
    Grid,
    Paper,
} from '@material-ui/core';
import { Alert } from '@material-ui/lab';

const LogoutCallback = ({ loading, error }) => {
    return (
        <div className="pt-5">
            <Container>
                <Grid container justify="center">
                    <Grid item md={6}>
                        <Paper>
                            <h2>Logg ut</h2>
                            {loading && (
                                <Box display="flex" justifyContent="center">
                                    <CircularProgress />
                                </Box>
                            )}
                            {error ? (
                                <Alert color="danger" className="mt-3">
                                    {error.message}
                                </Alert>
                            ) : (
                                ''
                            )}
                        </Paper>
                    </Grid>
                </Grid>
            </Container>
        </div>
    );
};

export default LogoutCallback;
