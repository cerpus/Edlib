import * as React from 'react';
import { CircularProgress } from '@material-ui/core';
import { Alert } from '@material-ui/lab';
const DefaultHookQuery = ({
    children,
    fetchData,
    backgroundUpdate = false,
}) => {
    const { loading, error, ...dataProps } = fetchData;

    if (loading && (!backgroundUpdate || dataProps.response == null)) {
        return (
            <div className="d-flex justify-content-center">
                <CircularProgress />
            </div>
        );
    }

    if (error) {
        return <Alert color="danger">{t('something_happened')}</Alert>;
    }

    return children(dataProps);
};

export default DefaultHookQuery;
