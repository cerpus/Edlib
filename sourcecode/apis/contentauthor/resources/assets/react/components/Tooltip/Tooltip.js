'use strict';

import React from 'react';
import PropTypes from 'prop-types';
import MuiTooltip from '@material-ui/core/Tooltip';
import { withStyles } from '@material-ui/core/styles';
import { FormattedMessage } from 'react-intl';

const StyledTooltip = withStyles((theme) => ({
    arrow: {
        color: theme.palette.common.black,
    },
    tooltip: {
        backgroundColor: theme.palette.common.black,
        fontSize: theme.typography.pxToRem(12),
    },
}))(MuiTooltip);

const Tooltip = ({message, messageId, children}) => {
    let msg = '';

    if (message) {
        msg = message;
    } else if (messageId) {
        msg = <FormattedMessage id={messageId}/>;
    }

    return (
        <StyledTooltip title={msg} placement="bottom" arrow>
            {children}
        </StyledTooltip>
    );
};

Tooltip.propTypes = {
    message: PropTypes.node,
    messageId: PropTypes.string,
    children: PropTypes.node
};

Tooltip.defaultProps = {
    message: null,
    messageId: null,
    children: null
};

export default Tooltip;
