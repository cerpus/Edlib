import React from 'react';
import { Container, Grid } from '@material-ui/core';
import GenerateCsvWithResourceUrls from '../components/GenerateCSVWithResourceUrls.jsx';

const Home = () => {
    return (
        <Container className="pt-3">
            <Grid container>
                <Grid item>
                    <h2>Home</h2>
                    <GenerateCsvWithResourceUrls />
                </Grid>
            </Grid>
        </Container>
    );
};

export default Home;
