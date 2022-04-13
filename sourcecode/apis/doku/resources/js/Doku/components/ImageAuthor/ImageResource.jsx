import React from 'react';
import AlignmentWrapper, { BaseToolbar } from '../../containers/AlignmentWrapper';
import { ImagePreview } from './';
import { useDokuContext } from '../../dokuContext';

export default ({ isEditing, data, block, entityKey, onUpdate }) => {
    const { openImageModal } = useDokuContext();

    return (
        <AlignmentWrapper
            block={block}
            size={data.size}
            align={data.align}
            isEditing={isEditing}
            onUpdate={onUpdate}
            entityKey={entityKey}
            toolbar={({ isFocused, left, ref }) => (
                <BaseToolbar
                    align={data.align}
                    data={data}
                    entityKey={entityKey}
                    isFocused={isFocused}
                    left={left}
                    onUpdate={onUpdate}
                    ref={ref}
                    onEdit={openImageModal}
                />
            )}
        >
            <ImagePreview file={data.file} metadata={data.metadata} />
        </AlignmentWrapper>
    );
};
