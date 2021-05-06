import React from 'react';
import useConfig from '../hooks/useConfig';
import useFetchWithToken from '../hooks/useFetchWithToken';
import { Spinner } from '@cerpus/ui';
import Lti from './Editors/Lti';
import Doku from './Editors/Doku';
import { useEdlibComponentsContext } from '../contexts/EdlibComponents';

const ResourceEditor = ({
    edlibId,
    onResourceReturned,
    translateToLanguage,
    type,
    loading = false,
}) => {
    const { edlib } = useConfig();
    const { language } = useEdlibComponentsContext();
    const url = React.useMemo(() => {
        if (edlibId) {
            return edlib(`/resources/v1/resources/${edlibId}/launch-editor`);
        }

        return edlib(`/resources/v1/launch-editor/${type}`);
    }, [edlibId, type]);

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

    if (!response) return <></>;

    if (response.editor === 'doku') {
        return <Doku editorData={response.data} />;
    }

    return <Lti data={response.data} onResourceReturned={onResourceReturned} />;
};

export default ResourceEditor;
