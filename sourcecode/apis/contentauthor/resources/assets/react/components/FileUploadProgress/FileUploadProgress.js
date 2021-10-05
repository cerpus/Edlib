import React from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';
import { Dialog, DialogContent, DialogTitle, LinearProgress, Typography } from '@material-ui/core';

const FileUploadProgress = ({
    total = 0,
    inProgress,
    show,
    done,
}) => {
    const progress = total > 0 ? (done / total) * 100 : 0;

    return (
        <Dialog
            open={show}
        >
            <DialogTitle>
                <FormattedMessage id="FILEUPLOADPROGRESS.GETTING_MEDIA_FILES_READY"/>
            </DialogTitle>
            <DialogContent>
                <LinearProgress variant="determinate" value={progress} valueBuffer={(done + inProgress)} />
                <Typography variant="body2" color="textSecondary">{`${done}`} / {`${total}`}</Typography>
            </DialogContent>
        </Dialog>
    );
};

FileUploadProgress.propTypes = {
    total: PropTypes.number,
    inProgress: PropTypes.number,
    show: PropTypes.bool,
    done: PropTypes.number,
};

export default FileUploadProgress;
