'use strict';

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { Tooltip as ToolTip, OverlayTrigger } from 'react-bootstrap';
import { FormattedHTMLMessage } from 'react-intl';

export default class FieldContainer extends Component {
    static defaultProps = {
        message: null,
        messageId: null,
        children: null
    };

    static propTypes = {
        message: PropTypes.node,
        messageId: PropTypes.string,
        children: PropTypes.node
    };

    render() {
        let message = '';

        if (this.props.message) {
            message = this.props.message;
        } else if (this.props.messageId) {
            message = <FormattedHTMLMessage id={this.props.messageId}/>;
        }

        return (
            <OverlayTrigger
                placement="bottom"
                overlay={
                    <ToolTip id="tooltip">
                        {message}
                    </ToolTip>
                }
            >
                {this.props.children}
            </OverlayTrigger>
        );
    }
};
