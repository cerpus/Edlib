import React from 'react';
import cn from 'classnames';
import styles from './login.module.scss';
import {
    Box,
    CircularProgress,
    Container,
    Grid,
    Paper,
} from '@material-ui/core';
import { Alert } from '@material-ui/lab';

const LoginCallback = ({ loading, error }) => {
    return (
        <div className={cn(styles.login)}>
            <Container>
                <Grid container justify="center">
                    <Grid item md={6}>
                        <Paper>
                            <h2>Logg inn</h2>
                            {loading && (
                                <Box justifyContent="center" display="flex">
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

export default LoginCallback;
