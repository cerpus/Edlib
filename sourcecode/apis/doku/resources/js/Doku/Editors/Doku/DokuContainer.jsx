import React from 'react';
import useFetchWithToken from '../../hooks/useFetchWithToken';
import { Alert, CircularProgress } from '@mui/material';
import Doku from './Doku';
import useConfig from '../../hooks/useConfig';

const DokuEdit = ({ dokuId }) => {
    const { edlib } = useConfig();
    const { error, loading, response } = useFetchWithToken(
        edlib('/dokus/v1/dokus/' + dokuId),
        'GET'
    );

    if (error) {
        return <Alert severity="error">Noe skjedde</Alert>;
    }

    if (loading || !response) {
        return <CircularProgress />;
    }

    return <Doku doku={response} />;
};

const DokuContainer = ({ editorData }) => {
    if (editorData && editorData.externalSystemId) {
        return <DokuEdit dokuId={editorData.externalSystemId} />;
    }

    return <Doku />;
};

export default DokuContainer;
