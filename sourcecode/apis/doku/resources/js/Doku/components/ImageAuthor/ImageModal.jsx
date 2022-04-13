import React from 'react';
import { FromSideModal } from '../FromSideModal';
import { ImageAuthor } from './';

export default ({ isOpen, onClose, onInsert, currentData }) => {
    return (
        <FromSideModal
            isOpen={isOpen}
            onClose={onClose}
        >
            <ImageAuthor
                currentData={currentData}
                onInsert={onInsert}
            />
        </FromSideModal>
    );
};
