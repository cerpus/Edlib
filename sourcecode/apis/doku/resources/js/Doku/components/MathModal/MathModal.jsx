import React from 'react';
import MathAuthor from '../MathAuthor';
import { FromSideModal } from '../FromSideModal';

const MathModal = ({ isOpen, onClose, onInsert, currentValue }) => {
    return (
        <FromSideModal
            isOpen={isOpen}
            onClose={onClose}
        >
            <MathAuthor
                currentValue={currentValue}
                onInsert={onInsert}
            />
        </FromSideModal>
    );
};

export default MathModal;
