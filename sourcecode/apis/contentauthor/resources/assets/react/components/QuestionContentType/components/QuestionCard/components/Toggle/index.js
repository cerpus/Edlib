import './Toggle.css';

import React from 'react';
import PropTypes from 'prop-types';
import Switch from '@material-ui/core/Switch';

function Toggle(props) {
    const {
        leftLabel = 'Correct',
        rightLabel = 'Wrong',
        isCorrect = true,
        onToggle,
    } = props;

    return (
        <div className="togglerSwitch">
            {leftLabel}
            <Switch
                onChange={onToggle}
                checked={isCorrect}
                color="default"
            />
            {rightLabel}
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
