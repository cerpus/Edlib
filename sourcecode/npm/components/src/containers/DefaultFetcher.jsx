import React from 'react';
import { Spinner, Alert } from '@cerpus/ui';

const DefaultFetcher = ({ useFetchData, children }) => {
    const { error, loading, data } = useFetchData;

    return (
        <div>
            {loading && <Spinner />}
            {error && <Alert>Noe skjedde</Alert>}
            {data && children({ data })}
        </div>
    );
};

export default DefaultFetcher;
