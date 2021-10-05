import React from 'react';
import PropTypes from 'prop-types';

import { DirectLinkContainer } from './components/DirectLink';

export default class LinkContentType extends React.Component {

    static defaultProps = {
        linkUrl: "",
        linkText: "",
        linkTitle: "",
        linkMetadata: {},
        linkForm: null
    }

    static propTypes = {
        linkUrl: PropTypes.string,
        linkTitle: PropTypes.string,
        linkText: PropTypes.string,
        linkMetadata: PropTypes.object,
        linkForm: PropTypes.object
    };

    constructor(props) {
        super(props);
    }

    render() {
        return (
            <DirectLinkContainer
                linkUrl={this.props.linkUrl}
                linkTitle={this.props.linkTitle}
                linkText={this.props.linkText}
                linkMetadata={this.props.linkMetadata}
                linkForm={this.props.linkForm}
            />
        );
    }
}
