import React from 'react';
import { CircularProgress, Alert } from '@mui/material';

const DefaultFetcher = ({ useFetchData, children }) => {
    const { error, loading, response } = useFetchData;

    return (
        <>
            {loading && !error && <CircularProgress />}
            {error && <Alert>Noe skjedde</Alert>}
            {response && children({ response })}
        </>
    );
};

export default DefaultFetcher;
