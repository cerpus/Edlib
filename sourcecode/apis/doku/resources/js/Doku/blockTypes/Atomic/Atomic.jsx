import React from 'react';
import { useDokuContext } from '../../dokuContext';
import EdlibResource from './blocks/EdlibResource';
import atomicTypes from '../../config/atomicTypes';
import EdlibUrlResource from './blocks/EdlibUrlResource';
import NdlaImageResource from './blocks/NdlaImageResource';
import Aside from './blocks/Aside';
import Box from './blocks/Box';
import NdlaH5pResource from './blocks/NdlaH5pResource';
import InlineTex from '../../decorators/Maths/InlineTex';

const DraggableWrapper = ({ children, block, draggable }) => {
    const { isEditing } = useDokuContext();

    const startDrag = (event) => {
        event.dataTransfer.dropEffect = 'move'; // eslint-disable-line no-param-reassign
        event.dataTransfer.setData('text', `DRAFTJS_BLOCK_KEY:${block.key}`);
    };

    return (
        <div
            onDragStart={isEditing && draggable ? startDrag : () => {}}
            draggable={draggable}
            contentEditable={false}
        >
            {children}
        </div>
    );
};

export default ({ block, ...props }) => {
    const { isEditing, onBlockUpdateData, usersForLti } = useDokuContext();

    let entityKey = block.getEntityAt(0);
    let entity = props.contentState.getEntity(entityKey);
    let data = entity.getData();
    let type = entity.getType();

    const draggable =
        [atomicTypes.SIDE_NOTE, atomicTypes.BOX].indexOf(type) === -1;

    return (
        <DraggableWrapper block={block} draggable={draggable}>
            {type === atomicTypes.EDLIB_RESOURCE && (
                <EdlibResource
                    entityKey={entityKey}
                    isEditing={isEditing}
                    data={data}
                    onUpdate={(newData) =>
                        onBlockUpdateData(entityKey, {
                            ...data,
                            ...newData,
                        })
                    }
                    usersForLti={usersForLti}
                    block={block}
                />
            )}
            {type === atomicTypes.EDLIB_URL_RESOURCE && (
                <EdlibUrlResource
                    isEditing={isEditing}
                    data={data}
                    onUpdate={(newData) =>
                        onBlockUpdateData(entityKey, {
                            ...data,
                            ...newData,
                        })
                    }
                    block={block}
                    entityKey={entityKey}
                />
            )}
            {type === atomicTypes.IMAGE && data.type === 'ndla' && (
                <NdlaImageResource
                    isEditing={isEditing}
                    data={data}
                    onUpdate={(newData) =>
                        onBlockUpdateData(entityKey, {
                            ...data,
                            ...newData,
                        })
                    }
                />
            )}
            {type === atomicTypes.SIDE_NOTE && (
                <Aside
                    data={data}
                    isEditing={isEditing}
                    onUpdate={(newData) =>
                        onBlockUpdateData(entityKey, {
                            ...data,
                            ...newData,
                        })
                    }
                />
            )}
            {type === atomicTypes.BOX && (
                <Box
                    data={data}
                    isEditing={isEditing}
                    onUpdate={(newData) =>
                        onBlockUpdateData(entityKey, {
                            ...data,
                            ...newData,
                        })
                    }
                />
            )}
            {type === atomicTypes.NDLA_EDLIB_RESOURCE && (
                <NdlaH5pResource data={data} isEditing={isEditing} />
            )}
            {type === atomicTypes.MATH && (
                <InlineTex entityKey={entityKey} />
            )}
        </DraggableWrapper>
    );
};
