import React from 'react';
import AlignmentWrapper from '../../../containers/AlignmentWrapper/AlignmentWrapper';
import FrameWithResize from '../../../../../components/FrameWithResize';

const NdlaH5pResource = ({ data, onUpdate }) => (
    <AlignmentWrapper
        size={data.size}
        align={data.align}
        block={data.block}
        onUpdate={onUpdate}
    >
        <FrameWithResize
            src={data.url}
            onPostMessage={(message) =>
                console.log(`${data.url} posted: `, message)
            }
        />
    </AlignmentWrapper>
);

export default NdlaH5pResource;
