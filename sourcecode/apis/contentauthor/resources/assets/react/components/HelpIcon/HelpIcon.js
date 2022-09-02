import React, { Component } from 'react';
import PropTypes from 'prop-types';
import Tooltip from '../Tooltip';

export default class HelpIcon extends Component {

    static propTypes = {
        messageId: PropTypes.string,
        messageString: PropTypes.node,
        className: PropTypes.string
    };

    static defaultProps =  {
        messageId: null,
        messageString: null,
        className: null,
    };

    render() {
        let help = null;
        let classes = 'fa fa-question-circle-o fa-fw helpicon-help';

        if (this.props.className) {
            classes += (' ' + this.props.className);
        }

        if (this.props.messageString || this.props.messageId) {
            help = (
                <Tooltip message={this.props.messageString} messageId={this.props.messageId}>
                    <i className={classes}/>
                </Tooltip>
            );
        }

        return help;
    }
};
