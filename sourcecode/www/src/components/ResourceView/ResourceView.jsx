import React from 'react';
import FrameWithResize from '../FrameWithResize';
import PostingFrame from '../PostingFrame';
import { Box } from '@mui/material';

const ResourceView = ({ preview }) => {
    if (preview.embedCode) {
        return (
            <Box
                sx={{
                    display: 'flex',
                    justifyContent: 'center',
                }}
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
            allowFullscreen={true}
        />
    );
};

export default ResourceView;
