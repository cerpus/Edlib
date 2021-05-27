import React from 'react';
import { Button, Spinner, Alert } from 'reactstrap';
import request from '../helpers/request.js';

const ReindexDokus = () => {
    const [{ loading, error, success }, setStatus] = React.useState({
        loading: false,
        error: false,
    });

    const onClick = React.useCallback(() => {
        setStatus({
            loading: true,
            error: false,
        });

        request('/dokus/v1/recommender/index-all', 'POST', { json: false })
            .then(() => {
                setStatus({
                    loading: false,
                    error: false,
                    success: true,
                });
            })
            .catch((error) => {
                setStatus({
                    loading: false,
                    error: true,
                });
            });
    }, []);

    return (
        <>
            {error && <Alert color="danger">Noe skjedde</Alert>}
            {success && <Alert color="success">Vellykket!</Alert>}
            <Button color="primary" onClick={onClick} disabled={loading}>
                {loading && <Spinner />}
                {!loading && 'Indekser dokus'}
            </Button>
        </>
    );
};

export default ReindexDokus;
