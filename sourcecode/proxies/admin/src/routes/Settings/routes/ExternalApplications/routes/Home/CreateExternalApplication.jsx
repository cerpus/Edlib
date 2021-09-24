import React from 'react';
import {
    Button,
    Dialog,
    DialogActions,
    DialogContent,
    DialogTitle,
    TextField,
} from '@material-ui/core';
import request from '../../../../../../helpers/request';
import useRequestAction from '../../../../../../hooks/useRequestAction';

const CreateExternalApplication = ({ isOpen, onClose, onAdded }) => {
    const [name, setName] = React.useState('');

    const { status, action } = useRequestAction((name) =>
        request('/common/applications', 'POST', {
            body: {
                name,
            },
        })
    );

    return (
        <Dialog open={isOpen} fullWidth maxWidth="xs">
            <DialogTitle>Lag ny ekstern applikasjon</DialogTitle>
            <DialogContent>
                <TextField
                    autoFocus
                    margin="dense"
                    label="Navn"
                    type="text"
                    fullWidth
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                />
            </DialogContent>
            <DialogActions>
                <Button onClick={onClose} color="primary">
                    Avbryt
                </Button>
                <Button
                    onClick={() => action(name, () => onAdded())}
                    color="primary"
                    disabled={status.loading}
                >
                    Lag ny
                </Button>
            </DialogActions>
        </Dialog>
    );
};

export default CreateExternalApplication;
