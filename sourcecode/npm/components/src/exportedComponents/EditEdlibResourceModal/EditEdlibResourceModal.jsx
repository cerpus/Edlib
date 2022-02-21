import React from 'react';
import EdlibIframe from '../../components/EdlibIframe';
import { Modal } from '@material-ui/core';

const EditEdlibResourceModal = ({
    removePadding,
    ltiLaunchUrl,
    onClose,
    header,
    onUpdateDone,
}) => {
    return (
        <Modal
            open={true}
            width="100%"
            onClose={onClose}
            style={{ margin: removePadding ? 0 : 20 }}
        >
            <div
                style={{
                    height: removePadding ? '100vh' : 'calc(100vh - 40px)',
                }}
            >
                <EdlibIframe
                    path="/s/edit-from-lti-link"
                    params={{
                        ltiLaunchUrl,
                        resourceTitle: header || 'Title',
                    }}
                    onAction={(data) => {
                        switch (data.messageType) {
                            case 'onClose':
                                onClose();
                                break;
                            case 'onUpdateDone':
                                onUpdateDone(data.extras);
                                break;
                            default:
                                break;
                        }
                    }}
                />
            </div>
        </Modal>
    );
};

export default EditEdlibResourceModal;
