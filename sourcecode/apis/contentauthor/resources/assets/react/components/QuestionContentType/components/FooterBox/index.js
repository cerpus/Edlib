import './FooterBox.scss';

import React from 'react';
import PropTypes from 'prop-types';
import { Button } from '@material-ui/core';
import { Undo } from '@material-ui/icons';

function FooterBox(props) {
    const {
        iconUrl,
        onBackClick,
        onGenerateClick,
        backButtonText,
        generateButtonText,
        processingForm,
    } = props;
    return (
        <div className="footerbox">
            <div className="leftFooter">
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
            <div className="rightFooter">
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
                    fullWidth={true}
                >
                    {generateButtonText}
                </Button>
            </div>
        </div>
    );
}

FooterBox.propTypes = {
    iconUrl: PropTypes.string,
    onBackClick: PropTypes.func,
    onGenerateClick: PropTypes.func,
    backButtonText: PropTypes.oneOfType([PropTypes.object, PropTypes.string]),
    generateButtonText: PropTypes.oneOfType([PropTypes.object, PropTypes.string]),
    processingForm: PropTypes.bool,
};

FooterBox.defaultProps = {
    processingForm: false,
};

export default FooterBox;
