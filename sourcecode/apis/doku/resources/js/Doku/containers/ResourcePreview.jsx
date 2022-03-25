import React from 'react';
import styled from 'styled-components';
import PostingFrame from '../components/PostingFrame';
import FrameWithResize from '../components/FrameWithResize';
import useGetResourcePreview from '../hooks/requests/useGetResourcePreview';

const DangerousContent = styled.div`
    display: flex;
    justify-content: center;
`;

const ResourcePreview = ({ preview }) => {
    if (preview.embedCode) {
        return (
            <DangerousContent
                dangerouslySetInnerHTML={{ __html: preview.embedCode }}
            />
        );
    }

    if (preview.method === 'GET') {
        return <FrameWithResize src={preview.url} />;
    }

    return (
        <PostingFrame
            frame={FrameWithResize}
            method={preview.method}
            params={preview.params}
            url={preview.url}
        />
    );
};

export default ({ resource, children }) => {
    const { loading, error, source, license, preview } = useGetResourcePreview(
        resource
    );

    return children({
        loading: !error && loading,
        error,
        frame: preview && <ResourcePreview preview={preview} />,
        source,
        license,
    });
};
