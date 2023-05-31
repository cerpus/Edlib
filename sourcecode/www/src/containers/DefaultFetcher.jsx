import React from 'react';
import { CircularProgress, Alert } from '@mui/material';
import useTranslation from '../hooks/useTranslation';

const DefaultFetcher = ({ useFetchData, children }) => {
    const { error, loading, response } = useFetchData;
    const { t } = useTranslation();

    return (
        <>
            {loading && !error && <CircularProgress />}
            {error && <Alert>{t('something_happened')}</Alert>}
            {response && children({ response })}
        </>
    );
};

export default DefaultFetcher;
