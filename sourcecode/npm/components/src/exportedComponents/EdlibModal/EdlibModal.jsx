import React from 'react';
import { Modal } from '@material-ui/core';

import EdlibModalContent from './EdlibModalContent';

const EdlibModal = ({ onClose, onResourceSelected, contentOnly = false }) => {
    if (contentOnly) {
        return <EdlibModalContent onClose={onClose} />;
    }

    return (
        <Modal
            open={true}
            width="100%"
            onClose={onClose}
            style={{ margin: 20 }}
        >
            <div>
                <EdlibModalContent
                    onClose={onClose}
                    onResourceSelected={onResourceSelected}
                    height="calc(100vh - 40px)"
                />
            </div>
        </Modal>
    );
};

export default EdlibModal;
