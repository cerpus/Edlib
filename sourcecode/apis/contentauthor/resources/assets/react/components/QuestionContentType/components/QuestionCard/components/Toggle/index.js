import './Toggle.css';

import React from 'react';
import PropTypes from 'prop-types';
import { Switch } from '@material-ui/core';

function Toggle(props) {
    const {
        leftLabel = 'Correct',
        rightLabel = 'Wrong',
        isCorrect = true,
        onToggle,
    } = props;

    return (
        <div className="togglerSwitch">
            <label>{leftLabel}</label>
            <Switch
                onChange={onToggle}
                checked={isCorrect}
                color="default"
            />
            <label>{rightLabel}</label>
        </div>
    );
}

Toggle.propTypes = {
    leftLabel: PropTypes.node,
    rightLabel: PropTypes.node,
    isCorrect: PropTypes.bool,
    onToggle: PropTypes.func,
};

export default Toggle;
