import React from 'react';
import { useState } from 'react';
import PropTypes from 'prop-types';
import DropZone from 'react-dropzone';
import Popover from '@material-ui/core/Popover';
import LinearProgress from '@material-ui/core/LinearProgress';
import ImageIcon from '@material-ui/icons/Image';
import DeleteIcon from '@material-ui/icons/Delete';
import { useIntl } from 'react-intl';

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

    const intl = useIntl();
    const [focused, setFocused] = useState(false);
    let icon = null;
    if ( previewImage === null) {
        icon = <ImageIcon
            style={{ cursor: 'pointer' }}
            tabIndex={0}
            onFocus={() => setFocused(true)}
            onBlur={() => setFocused(false)}
        />;
        if ( readOnly === false ) {
            icon = (
                <DropZone
                    onDropAccepted={onDrop}
                    multiple={false}
                    className={`imageDropzone ${focused ? 'focused' : ''}`}
                    disabled={readOnly}
                    inputProps={{
                        'aria-label': intl.formatMessage({
                            id: 'QUESTIONCARD.ADD_IMAGE_LABEL',
                        })
                    }}
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
