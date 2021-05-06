import React from 'react';
import { FromSideModal } from '../../../components/FromSideModal';
import ResourceEditor from '../../../components/ResourceEditor';
import { useDokuContext } from '../dokuContext';

const EditResourceModal = ({ updateEdlibResourceData = null, onClose }) => {
    const { onBlockUpdateData } = useDokuContext();

    return (
        <FromSideModal
            isOpen={updateEdlibResourceData}
            onClose={onClose}
            usePortal={false}
        >
            {updateEdlibResourceData && (
                <>
                    <ResourceEditor
                        edlibId={updateEdlibResourceData.data.edlibId}
                        onResourceReturned={(edlibId) => {
                            onBlockUpdateData(
                                updateEdlibResourceData.entityKey,
                                { ...updateEdlibResourceData.data, edlibId }
                            );
                            onClose();
                        }}
                    />
                </>
            )}
        </FromSideModal>
    );
};

export default EditResourceModal;
