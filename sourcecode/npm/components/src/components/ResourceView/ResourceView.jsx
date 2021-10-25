import React from 'react';
import styled from 'styled-components';
import FrameWithResize from '../FrameWithResize.jsx';
import PostingFrame from '../PostingFrame.jsx';

const DangerousContent = styled.div`
    display: flex;
    justify-content: center;
`;

const ResourceView = ({ preview }) => {
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

export default ResourceView;
