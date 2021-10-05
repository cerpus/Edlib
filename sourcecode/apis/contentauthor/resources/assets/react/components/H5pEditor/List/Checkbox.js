import React from 'react';
import { FormattedMessage } from 'react-intl';

const Checkbox = ({ checked, onToggle, labelId }) => {
    return (
        <div onClick={onToggle}>
            <input
                type="checkbox"
                checked={checked}
                onChange={() => onToggle()}
            /><label style={{ marginLeft: 5 }}><FormattedMessage id={labelId} /></label>
        </div>
    );
};

export default Checkbox;
