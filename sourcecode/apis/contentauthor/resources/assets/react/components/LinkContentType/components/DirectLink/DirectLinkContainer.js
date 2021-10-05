import React from 'react';
import PropTypes from 'prop-types';
import Axios from 'axios';
import Validator from 'validator';
import { Debounce } from 'utils/utils';

import DirectLinkLayout from './DirectLinkLayout';

export default class DirectLinkContainer extends React.Component {

    static propTypes = {
        linkUrl: PropTypes.string,
        linkTitle: PropTypes.string,
        linkText: PropTypes.string,
        linkMetadata: PropTypes.object,
        linkForm: PropTypes.object,
    };

    static defaultProps = {
        linkUrl: "",
        linkTitle: "",
        linkText: "",
        linkMetadata: {},
        linkForm: null
    };
    loadMetadata = Debounce(({url}) => {
        if (Validator.isURL(url)) {
            Axios.get("/v1/link/embeddata?link=" + url)
                .then((response) => {
                    let data = Object.assign({}, response.data, {validUrl: true});
                    this.handleMetadata(data);
                }).catch(() => {
                this.handleMetadata({
                    validUrl: false,
                    loading: false
                });
            });
        } else {
            this.handleMetadata({
                validUrl: false,
                loading: false
            });
        }
    }, 300, false);

    constructor(props) {
        super(props);

        this.state = {
            url: props.linkUrl,
            title: props.linkTitle,
            linktext: props.linkText,
            loading: false,
            metadata: props.linkMetadata !== null ? props.linkMetadata : {},
            validUrl: false,
        };

        this.handleChangeUrl = this.handleChangeUrl.bind(this);
        this.handleChangeTitle = this.handleChangeTitle.bind(this);
        this.handleChangeLinkText = this.handleChangeLinkText.bind(this);
        this.handleMetadata = this.handleMetadata.bind(this);

        const {linkForm} = this.props;
        if (linkForm != null) {
            linkForm.addEventListener('submit', (event) => {
                if (this.state.validUrl === false || this.state.loading === true) {
                    event.preventDefault();
                }
            });
        }
    }

    handleMetadata(data) {
        this.setState({
            metadata: data,
            loading: false,
            validUrl: data.validUrl,
        });
    }


    handleChangeUrl(event) {
        const validUrl = Validator.isURL(event.target.value);
        this.setState({
            url: event.target.value,
            metadata: {
                validUrl: validUrl,
            },
            loading: validUrl,
        }, () => (this.loadMetadata(this.state)));
    }

    handleChangeTitle(event) {
        this.setState({title: event.target.value});
    }

    handleChangeLinkText(event) {
        this.setState({linktext: event.target.value});
    }

    render() {
        return (
            <DirectLinkLayout
                url={this.state.url}
                onChangeUrl={this.handleChangeUrl}
                metadata={this.state.metadata}
                loading={this.state.loading}
            />
        );
    }
}
