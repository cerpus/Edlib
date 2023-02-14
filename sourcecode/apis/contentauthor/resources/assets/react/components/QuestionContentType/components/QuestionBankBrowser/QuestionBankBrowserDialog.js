import * as React from 'react';
import * as PropTypes from 'prop-types';
import Dialog from '@material-ui/core/Dialog';
import DialogTitle from '@material-ui/core/DialogTitle';
import DialogContent from '@material-ui/core/DialogContent';

function QuestionBankBrowserDialog({ title, open, onRequestClose, children }) {
    return (
        <Dialog
            open={open}
            onClose={onRequestClose}
            scroll="paper"
            fullWidth={true}
        >
            <DialogTitle>{title}</DialogTitle>
            <DialogContent>
                {children}
            </DialogContent>
        </Dialog>
    );
}

QuestionBankBrowserDialog.propTypes = {
    title: PropTypes.node,
    open: PropTypes.bool,
    onRequestClose: PropTypes.func,
};

QuestionBankBrowserDialog.defaultProps = {
    title: null,
    open: false,
    onRequestClose: null,
};

export default QuestionBankBrowserDialog;
