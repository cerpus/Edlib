import React from 'react';
import { Button, CircularProgress } from '@material-ui/core';
import { Alert } from '@material-ui/lab';
import apiConfig from '../config/api.js';
import useRequestWithToken from '../hooks/useRequestWithToken.jsx';

const listToCSVContent = (list) =>
    'data:text/csv;charset=utf-8,' + list.map((e) => e.join(',')).join('\n');

const GenerateCsvWithResourceUrls = () => {
    const request = useRequestWithToken();

    const [{ loading, error, success }, setStatus] = React.useState({
        loading: false,
        error: false,
    });

    const onClick = React.useCallback(() => {
        setStatus({
            loading: true,
            error: false,
        });

        request('/resources/admin/resources', 'GET', { json: true })
            .then(({ resources }) => {
                const link = document.createElement('a');
                const rows = resources.map((r) => [
                    r.id,
                    `${apiConfig.wwwUrl}/s/resources/${r.id}`,
                ]);
                rows.unshift(['id', 'url']);
                link.setAttribute('href', encodeURI(listToCSVContent(rows)));
                link.setAttribute('download', 'edlib_resources.csv');
                document.body.appendChild(link); // Required for FF

                link.click();

                setStatus({
                    loading: false,
                    error: false,
                    success: true,
                });
            })
            .catch((error) => {
                console.error(error);
                setStatus({
                    loading: false,
                    error: true,
                });
            });
    }, []);

    return (
        <>
            {error && <Alert color="danger">{t('something_happened')}</Alert>}
            {success && <Alert color="success">Vellykket!</Alert>}
            <Button color="primary" onClick={onClick} disabled={loading}>
                {loading && <CircularProgress />}
                {!loading && 'Lag CSV med alle ressurser'}
            </Button>
        </>
    );
};

export default GenerateCsvWithResourceUrls;
