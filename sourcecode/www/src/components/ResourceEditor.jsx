import React from 'react';
import useFetchWithToken from '../hooks/useFetchWithToken';
import { CircularProgress } from '@mui/material';
import Lti from './Editors/Lti';
import { useEdlibComponentsContext } from '../contexts/EdlibComponents';
import queryString from 'query-string';
import { useLocation } from 'react-router-dom';
import { useConfigurationContext } from '../contexts/Configuration.jsx';

const ResourceEditor = ({
    edlibId,
    onResourceReturned,
    translateToLanguage,
    type,
    loading = false,
}) => {
    const { edlibApi } = useConfigurationContext();
    const { language } = useEdlibComponentsContext();
    const location = useLocation();

    const group = React.useMemo(() => {
        const query = queryString.parse(location.search);

        return query.group ? query.group : null;
    }, [location]);

    const url = React.useMemo(() => {
        if (edlibId) {
            return edlibApi(`/lti/v2/resources/${edlibId}`);
        }

        let createPath = `/lti/v2/editors/${type}/launch`;
        if (group) {
            createPath += `?group=${group}`;
        }

        return edlibApi(createPath);
    }, [edlibId, type, group]);

    const { error, fetchLoading, response } = useFetchWithToken(
        url,
        'POST',
        React.useMemo(
            () => ({
                body: {
                    translateToLanguage,
                    language,
                },
            }),
            [translateToLanguage]
        )
    );

    if (fetchLoading || loading)
        return (
            <div
                style={{
                    display: 'flex',
                    justifyContent: 'center',
                    marginTop: 10,
                }}
            >
                <CircularProgress size={30} />
            </div>
        );

    if (!response) return <></>;

    return <Lti data={response} onResourceReturned={onResourceReturned} />;
};

export default ResourceEditor;
