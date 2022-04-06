import React from 'react';
import useConfig from '../hooks/useConfig';
import useFetchWithToken from '../hooks/useFetchWithToken';
import { CircularProgress } from '@mui/material';
import Lti from '../Editors/Lti';
import Doku from '../Editors/Doku';
import { useEdlibComponentsContext } from '../contexts/EdlibComponents';
import queryString from 'query-string';
import { useLocation } from 'react-router-dom';
import resourceTypes from '../config/resourceTypes';

const ResourceEditor = ({
    edlibId,
    onResourceReturned,
    translateToLanguage,
    type,
    loading = false,
}) => {
    const { edlib } = useConfig();
    const { language } = useEdlibComponentsContext();
    const location = useLocation();

    const group = React.useMemo(() => {
        const query = queryString.parse(location.search);

        return query.group ? query.group : null;
    }, [location]);

    const url = React.useMemo(() => {
        if (edlibId) {
            return edlib(`/lti/v2/resources/${edlibId}`);
        }

        let createPath;
        if (type !== resourceTypes.URL) {
            createPath = `/lti/v2/editors/contentauthor/launch?group=${type.toLowerCase()}`;
        } else {
            createPath = `/lti/v2/editors/${type}/launch`;
            if (group) {
                createPath += `?group=${group}`;
            }
        }

        return edlib(createPath);
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

    if (response.editor === 'doku') {
        return <Doku editorData={response.data} />;
    }

    return <Lti data={response} onResourceReturned={onResourceReturned} />;
};

export default ResourceEditor;
