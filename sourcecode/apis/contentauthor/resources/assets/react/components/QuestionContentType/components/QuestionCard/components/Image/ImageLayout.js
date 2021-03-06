import React from 'react';
import PropTypes from 'prop-types';
import DropZone from 'react-dropzone';
import { Popover, LinearProgress } from '@material-ui/core';
import { Image as ImageIcon, Delete as DeleteIcon } from '@material-ui/icons';

function ImageLayout(props) {
    const {
        onDrop,
        previewImage,
        enlargeImage = false,
        onClick,
        anchorElement,
        onRemoveImage,
        uploadProgress,
        readOnly = false,
        uploading,
    } = props;

    let icon = null;
    if ( previewImage === null) {
        icon = <ImageIcon />;
        if ( readOnly === false ) {
            icon = (
                <DropZone
                    onDropAccepted={onDrop}
                    multiple={false}
                    className="imageDropzone"
                    disabled={readOnly}
                >
                    {icon}
                </DropZone>);
        }
    }

    return (
        <div
            className={'imageContainer ' + (previewImage !== null ? 'withImage' : null)}
        >
            {icon}
            {previewImage !== null && (
                <div>
                    {uploading === true && uploadProgress < 100 && (
                        <LinearProgress
                            variant="determinate"
                            value={uploadProgress}
                            className="uploadProgress"
                        />
                    )}
                    <img src={previewImage} onClick={onClick} alt="assigment_image" />
                    <Popover
                        open={enlargeImage}
                        onClose={onClick}
                        anchorEl={anchorElement}
                        anchorOrigin={{
                            vertical: 'center',
                            horizontal: 'center',
                        }}
                        transformOrigin={{
                            vertical: 'center',
                            horizontal: 'center',
                        }}
                    >
                        <div className="popoverContainer">
                            <img src={previewImage} />
                            {readOnly === false && (
                                <div onClick={onRemoveImage}>
                                    <DeleteIcon />
                                </div>
                            )}
                        </div>
                    </Popover>
                </div>
            )}
        </div>
    );
}

ImageLayout.propTypes = {
    onDrop: PropTypes.func,
    previewImage: PropTypes.string,
    enlargeImage: PropTypes.bool,
    onClick: PropTypes.func,
    anchorElement: PropTypes.object,
    onRemoveImage: PropTypes.func,
    uploadProgress: PropTypes.number,
    readOnly: PropTypes.bool,
    uploading: PropTypes.bool,
};

ImageLayout.defaultProps = {
    uploading: false,
};

export default ImageLayout;
