import React from 'react';
import styled from 'styled-components';
import PostingFrame from '../components/PostingFrame';
import useConfig from '../hooks/useConfig';
import FrameWithResize from '../components/FrameWithResize';
import useFetchWithToken from '../hooks/useFetchWithToken';
import { Spinner } from '@cerpus/ui';

const StyledPostingFrame = styled(PostingFrame)`
    width: 100%;
    height: 100%;
`;

const ContentAuthor = ({
    edlibId,
    onResourceReturned,
    translateToLanguage,
    type,
}) => {
    const { edlib } = useConfig();
    const url = React.useMemo(() => {
        if (edlibId) {
            return edlib(
                `/resources/v1/resources/${edlibId}/launch-lti-editor`
            );
        }

        return edlib(`/resources/v1/launch-lti-editor/${type}`);
    }, [edlibId, type]);

    const { error, loading, response } = useFetchWithToken(
        url,
        'POST',
        React.useMemo(
            () => ({
                body: {
                    translateToLanguage,
                },
            }),
            [translateToLanguage]
        )
    );

    if (!response) return <></>;

    if (loading) return <Spinner />;

    return (
        <StyledPostingFrame
            frame={FrameWithResize}
            method="POST"
            url={response.url}
            params={response.params}
            onPostMessage={(event) => {
                if (typeof event.data === 'string') {
                    onResourceReturned(event.data);
                }
            }}
        />
    );
};

export default ContentAuthor;
