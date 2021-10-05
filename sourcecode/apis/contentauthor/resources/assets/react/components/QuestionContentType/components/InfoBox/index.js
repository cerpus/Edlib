import './InfoBox.scss';

import React from 'react';
import PropTypes from 'prop-types';
import { Button } from '@material-ui/core';
import { Undo } from '@material-ui/icons';

function InfoBox(props) {
    const {
        infoText,
        iconUrl,
        onBackClick,
        onGenerateClick,
        backButtonText,
        generateButtonText,
        processingForm,
    } = props;
    return (
        <div className="infobox">
            <div className="infotext">
                {infoText}
            </div>
            <div className="infoactions">
                <img
                    src={iconUrl}
                    alt="image"
                    className="infoicon"
                />
                <Button
                    variant="outlined"
                    onClick={onGenerateClick}
                    className="generate"
                    disabled={processingForm}
                >
                    {generateButtonText}
                </Button>
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
            </div>
        </div>
    );
}

InfoBox.propTypes = {
    infoText: PropTypes.oneOfType([PropTypes.object, PropTypes.string]),
    iconUrl: PropTypes.string,
    onBackClick: PropTypes.func,
    onGenerateClick: PropTypes.func,
    backButtonText: PropTypes.oneOfType([PropTypes.object, PropTypes.string]),
    generateButtonText: PropTypes.oneOfType([PropTypes.object, PropTypes.string]),
    processingForm: PropTypes.bool,
    onChangeProcessing: PropTypes.func,
};

InfoBox.defaultProps = {
    processingForm: false,
};

export default InfoBox;
