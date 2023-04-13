import React, { Component } from 'react';
import PropTypes from 'prop-types';
import Dialog from '@material-ui/core/Dialog';
import DialogContent from '@material-ui/core/DialogContent';

class DialogComponent extends Component {
    static propTypes = {
        onToggle: PropTypes.func.isRequired,
        maxWidth: PropTypes.oneOf(['xs', 'sm', 'md', 'lg', 'xl', false]),
        open: PropTypes.bool,
        scroll: PropTypes.string,
    };

    static defaultProps = {
        maxWidth: 'md',
        open: false,
        scroll: 'paper',
    };

    constructor(props) {
        super(props);

        this.state = {
            dialogOpen: props.open,
        };

        this.handleToggle = this.handleToggle.bind(this);

        props.onToggle(this.handleToggle);
    }


    handleToggle() {
        this.setState({
            dialogOpen: !this.state.dialogOpen,
        });
    }

    render() {
        const {
            maxWidth,
            scroll,
        } = this.props;
        return (
            <Dialog
                open={this.state.dialogOpen}
                scroll={scroll}
                fullWidth={true}
                maxWidth={maxWidth}
                onClose={this.handleToggle}
            >
                <DialogContent>
                    {this.props.children}
                </DialogContent>
            </Dialog>
        );
    }
}

export default DialogComponent;
