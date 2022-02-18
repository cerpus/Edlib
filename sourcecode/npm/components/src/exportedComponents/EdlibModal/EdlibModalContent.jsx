import React from 'react';
import EdlibIframe from '../../components/EdlibIframe';

const EdlibModalContent = ({
    onClose,
    onResourceSelected,
    height = '100%',
}) => {
    return (
        <EdlibIframe
            height={height}
            path="/s/content-explorer"
            onAction={(data) => {
                switch (data.messageType) {
                    case 'onClose':
                        onClose();
                        break;
                    case 'onLtiResourceSelected':
                        onResourceSelected(data.extras);
                        break;
                    default:
                        break;
                }
            }}
        />
    );
};

export default EdlibModalContent;
