import React from 'react';
import _ from 'lodash';
import { useIframeStandaloneContext } from '../../../contexts/IframeStandalone.jsx';
import { useResourceCapabilities } from '../../../contexts/ResourceCapabilities.jsx';
import { useHistory } from 'react-router-dom';
import useArray from '../../../hooks/useArray.js';
import { Button, Dialog, DialogActions, DialogTitle } from '@mui/material';
import ResourceModal from '../../ResourceModal/ResourceModal.jsx';
import useTranslation from '../../../hooks/useTranslation.js';

const ViewContainer = ({ children, showDeleteButton }) => {
    const { t } = useTranslation();
    const { getPath } = useIframeStandaloneContext();
    const { onInsert, onRemove } = useResourceCapabilities();
    const idsToHide = useArray();
    const history = useHistory();

    const [selectedResource, setSelectedResource] = React.useState(null);
    const [showConfirmDeletionModal, setShowConfirmDeletionModal] =
        React.useState(false);
    const [currentEditContextId, setCurrentEditContextId] =
        React.useState(null);

    return (
        <>
            {children({
                cogProps: (resource) => {
                    return {
                        resource,
                        showDeleteButton: showDeleteButton,
                        onOpen: () => setCurrentEditContextId(resource.id),
                        onClose: () => setCurrentEditContextId(null),
                        isOpen: resource.id === currentEditContextId,
                        onEdit: () => {
                            setCurrentEditContextId(null);
                            history.push(getPath(`/resources/${resource.id}`));
                        },
                        onTranslate: () => {
                            setCurrentEditContextId(null);
                            history.push(
                                getPath(`/resources/${resource.id}/nno`)
                            );
                        },
                        onUse: async () => {
                            setCurrentEditContextId(null);
                            await onInsert(
                                resource.id,
                                resource.version.id,
                                resource.version.title
                            );
                        },
                        onRemove: () =>
                            setShowConfirmDeletionModal(resource.id),
                    };
                },
                setSelectedResource,
            })}
            <ResourceModal
                isOpen={!!selectedResource}
                onClose={() => setSelectedResource(null)}
                resource={selectedResource}
            />
            <Dialog
                open={showConfirmDeletionModal}
                onClose={() => setShowConfirmDeletionModal(false)}
                maxWidth="sm"
                fullWidth
            >
                <DialogTitle>{_.capitalize(t('are_you_sure'))}?</DialogTitle>
                <DialogActions>
                    <Button
                        color="primary"
                        variant="outlined"
                        onClick={() => setShowConfirmDeletionModal(false)}
                    >
                        {_.capitalize(t('close'))}
                    </Button>
                    <Button
                        color="primary"
                        variant="contained"
                        style={{ marginLeft: 5 }}
                        onClick={() => {
                            onRemove(showConfirmDeletionModal).then(() => {
                                idsToHide.push(showConfirmDeletionModal);
                                setShowConfirmDeletionModal(false);
                            });
                        }}
                    >
                        {_.capitalize(t('delete'))}
                    </Button>
                </DialogActions>
            </Dialog>
        </>
    );
};

export default ViewContainer;
