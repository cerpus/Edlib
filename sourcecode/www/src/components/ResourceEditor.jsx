import React from 'react';
import useConfig from '../hooks/useConfig';
import useFetchWithToken from '../hooks/useFetchWithToken';
import { Spinner } from '@cerpus/ui';
import Lti from './Editors/Lti';
import { useEdlibComponentsContext } from '../contexts/EdlibComponents';
import queryString from 'query-string';
import { useLocation } from 'react-router-dom';

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

        let createPath = `/lti/v2/editors/${type}/launch`;
        if (group) {
            createPath += `?group=${group}`;
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
                <Spinner size={30} />
            </div>
        );

    if (!response || response.editor === 'doku') return <></>;

    return <Lti data={response} onResourceReturned={onResourceReturned} />;
};

export default ResourceEditor;
