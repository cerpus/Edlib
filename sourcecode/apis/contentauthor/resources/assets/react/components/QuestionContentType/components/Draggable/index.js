import React from 'react';
import PropTypes from 'prop-types';
import { Draggable } from 'react-beautiful-dnd';

function draggable(props){
    const {
        children,
        dragKey,
        index,
    } = props;
    return (
        <Draggable
            key={dragKey}
            draggableId={dragKey}
            index={index}
        >
        {(provided, snapshot) => (
            <div ref={provided.innerRef}
                 {...provided.draggableProps}
                 {...provided.dragHandleProps}>
                {children}
            </div>
        )}
        </Draggable>
    );
}

draggable.propTypes = {
    dragKey: PropTypes.string.isRequired,
    index: PropTypes.number,
};

export default draggable;