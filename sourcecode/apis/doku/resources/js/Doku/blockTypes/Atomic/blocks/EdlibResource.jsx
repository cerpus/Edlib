import React from 'react';
import ResourcePreviewContainer from '../../../containers/ResourcePreview';
import AlignmentWrapper from '../../../containers/AlignmentWrapper/AlignmentWrapper';
import LtiLaunch from '../../../components/LtiLaunch';
import { useDokuContext } from '../../../dokuContext';
import { BaseToolbar } from '../../../containers/AlignmentWrapper';

const EdlibResource = ({ data, onUpdate, usersForLti, block, entityKey }) => {
    const { isEditing, setEditEdlibResourceData } = useDokuContext();
console.log(data);
    return (
        <AlignmentWrapper
            data={data}
            align={data.align}
            onUpdate={onUpdate}
            block={block}
            entityKey={entityKey}
            toolbar={({ isFocused, left, ref }) => (
                <BaseToolbar
                    align={data.align}
                    data={data}
                    entityKey={entityKey}
                    isFocused={isFocused}
                    left={left}
                    onUpdate={onUpdate}
                    setEditEdlibResourceData={setEditEdlibResourceData}
                    ref={ref}
                />
            )}
        >
            {!isEditing && (
                <LtiLaunch
                    launchUrl={data.launchUrl}
                    usersForLti={usersForLti}
                />
            )}
            {isEditing && (
                <ResourcePreviewContainer
                    key={data.align}
                    resource={{ id: data.edlibId, version: {id: data.resourceVersionId} }}
                >
                    {({ frame }) => frame || <div>loading</div>}
                </ResourcePreviewContainer>
            )}
        </AlignmentWrapper>
    );
};

export default EdlibResource;
