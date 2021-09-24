import React from 'react';
import {
    Button,
    Dialog,
    DialogActions,
    DialogContent,
    DialogTitle,
} from '@material-ui/core';
import { Delete } from '@material-ui/icons';
import request from '../../../../../../helpers/request';
import useRequestAction from '../../../../../../hooks/useRequestAction';
import ApplicationContext from '../../../../../../contexts/application';

const ConfirmDelete = ({ accessTokenToDelete, onClose, onDeleted }) => {
    const { id: applicationId } = React.useContext(ApplicationContext);
    const {
        status: deleteAccessTokenStatus,
        action: deleteAccessToken,
    } = useRequestAction(() =>
        request(
            `/common/applications/${applicationId}/access_tokens/${accessTokenToDelete}`,
            'DELETE'
        )
    );

    return (
        <Dialog open={!!accessTokenToDelete} fullWidth maxWidth="xs">
            <DialogTitle>Delete application access token?</DialogTitle>
            <DialogContent>
                Are you sure you want to delete this access token? Application
                using this token will loose access to the API.
            </DialogContent>
            <DialogActions>
                <Button onClick={onClose} color="primary">
                    Cancel
                </Button>
                <Button
                    onClick={() => deleteAccessToken('', () => onDeleted())}
                    color="error"
                    disabled={deleteAccessTokenStatus.loading}
                    startIcon={<Delete />}
                >
                    Delete
                </Button>
            </DialogActions>
        </Dialog>
    );
};

export default ConfirmDelete;
