import './InfoBox.scss';

import React from 'react';
import PropTypes from 'prop-types';
import clsx from 'clsx';
import Button from '@material-ui/core/Button';
import Undo from '@material-ui/icons/Undo';

function InfoBox(props) {
    const {
        infoText,
        iconUrl,
        onBackClick,
        onGenerateClick,
        backButtonText,
        generateButtonText,
        processingForm,
        type,
    } = props;
    return (
        <div
            className={clsx("infobox", {'headerbox': type === 'header'})}
        >
            <div className="infotext">
                {infoText}
            </div>
            <div className="infoactions">
                <img
                    src={iconUrl}
                    alt="image"
                    className="infoicon"
                />
                {onGenerateClick && (
                    <Button
                        variant="contained"
                        onClick={onGenerateClick}
                        className="generate"
                        disabled={processingForm}
                    >
                        {generateButtonText}
                    </Button>
                )}
                {onBackClick && (
                    <Button
                        variant="outlined"
                        onClick={onBackClick}
                        className="goback"
                        color="inherit"
                        fullWidth={true}
                    >
                        <Undo />
                        {backButtonText}
                    </Button>
                )}
            </div>
        </div>
    );
}

InfoBox.propTypes = {
    infoText: PropTypes.node,
    iconUrl: PropTypes.string,
    onBackClick: PropTypes.func,
    onGenerateClick: PropTypes.func,
    backButtonText: PropTypes.oneOfType([PropTypes.object, PropTypes.string]),
    generateButtonText: PropTypes.oneOfType([PropTypes.object, PropTypes.string]),
    processingForm: PropTypes.bool,
    onChangeProcessing: PropTypes.func,
    type: PropTypes.oneOf(['info', 'header']),
};

InfoBox.defaultProps = {
    processingForm: false,
    type: 'info',
};

export default InfoBox;
