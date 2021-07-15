import React from 'react';
import authContext from '../contexts/auth.js';
import { Button, Container, Grid } from '@material-ui/core';

const Login = () => {
    const { loginUrl } = React.useContext(authContext);
    return (
        <Container className="pt-3">
            <Grid container>
                <Grid item>
                    <h1>Login</h1>
                    <p>Du må logge inn for å bruke denne siden.</p>
                    <Button color="primary" component="a" href={loginUrl}>
                        Logg inn
                    </Button>
                </Grid>
            </Grid>
        </Container>
    );
};

export default Login;
