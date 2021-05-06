import React from 'react';
import FrameWithResize from '../FrameWithResize';
import styled from 'styled-components';
import PostingFrame from '../PostingFrame';

const StyledPostingFrame = styled(PostingFrame)`
    width: 100%;
    height: 100%;
    display: block;
`;

const Lti = ({ data, onResourceReturned }) => {
    return (
        <StyledPostingFrame
            frame={FrameWithResize}
            method="POST"
            url={data.url}
            params={data.params}
            onPostMessage={(event) => {
                if (typeof event.data === 'string') {
                    onResourceReturned(event.data);
                }
            }}
        />
    );
};

export default Lti;
