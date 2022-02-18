import React from 'react';
import { Spinner, Alert } from '@cerpus/ui';

const DefaultFetcher = ({ useFetchData, children }) => {
    const { error, loading, response } = useFetchData;

    return (
        <>
            {loading && !error && <Spinner />}
            {error && <Alert>Noe skjedde</Alert>}
            {response && children({ response })}
        </>
    );
};

export default DefaultFetcher;
