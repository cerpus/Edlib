import './Toggle.css';

import React from 'react';
import PropTypes from 'prop-types';
import Switch from '@material-ui/core/Switch';
import clsx from 'clsx';

function Toggle(props) {
    const {
        leftLabel = 'Correct',
        rightLabel = 'Wrong',
        isCorrect = true,
        onToggle,
    } = props;

    return (
        <div className="togglerSwitch">
            <span className={clsx({'labelSelected': !isCorrect})}>
                {leftLabel}
            </span>
            <Switch
                onChange={onToggle}
                checked={isCorrect}
                color="default"
            />
            <span className={clsx({'labelSelected': isCorrect})}>
                {rightLabel}
            </span>
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
