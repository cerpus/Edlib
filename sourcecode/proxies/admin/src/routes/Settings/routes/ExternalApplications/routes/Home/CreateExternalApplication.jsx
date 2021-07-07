import React from 'react';
import {
    Button,
    Dialog,
    DialogActions,
    DialogContent,
    DialogTitle,
    TextField,
} from '@material-ui/core';

const CreateExternalApplication = ({ isOpen, onClose }) => {
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
                />
            </DialogContent>
            <DialogActions>
                <Button onClick={onClose} color="primary">
                    Avbryt
                </Button>
                <Button onClick={onClose} color="primary">
                    Lag ny
                </Button>
            </DialogActions>
        </Dialog>
    );
};

export default CreateExternalApplication;
