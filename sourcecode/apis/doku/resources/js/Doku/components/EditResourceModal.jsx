import React from 'react';
import { FromSideModal } from '../components/FromSideModal';
import ResourceEditor from '../components/ResourceEditor';
import { useDokuContext } from '../dokuContext';

const EditResourceModal = ({ updateEdlibResourceData = null, onClose }) => {
    const { onBlockUpdateData } = useDokuContext();

    return (
        <FromSideModal
            isOpen={updateEdlibResourceData}
            onClose={onClose}
        >
            {updateEdlibResourceData && (
                <>
                    <ResourceEditor
                        edlibId={updateEdlibResourceData.data.edlibId}
                        onResourceReturned={({ resourceId: edlibId, resourceVersionId }) => {
                            onBlockUpdateData(
                                updateEdlibResourceData.entityKey,
                                { ...updateEdlibResourceData.data, edlibId, resourceVersionId }
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
