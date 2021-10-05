'use strict';

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { Modal } from 'react-bootstrap';

export default class ModalWindow extends Component {
    static propTypes = {
        show: PropTypes.bool,
        onHide: PropTypes.func,
        header: PropTypes.node,
        footer: PropTypes.node,
        children: PropTypes.node
    };

    static defaultProps = {
        show: false,
        onHide: null,
        header: null,
        footer: null
    };

    render() {
        return (
            <Modal show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header closeButton={true}>
                    <Modal.Title>
                        {this.props.header}
                    </Modal.Title>
                </Modal.Header>
                <Modal.Body>
                    {this.props.children}
                </Modal.Body>
                <Modal.Footer>
                    {this.props.footer}
                </Modal.Footer>
            </Modal>
        );
    }
}
