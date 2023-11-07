import React, { Component } from 'react';
import PropTypes from 'prop-types';
import Axios from '../../utils/axiosSetup';
import ImageBrowserLayout from './ImageBrowserLayout';
import { injectIntl } from 'react-intl';

class ImageBrowserContainer extends Component {
    static propTypes = {
        searchUrl: PropTypes.string,
        onSelect: PropTypes.func.isRequired,
        locale: PropTypes.string,
        onToggle: PropTypes.func,
        getCurrentLanguage: PropTypes.func,
        apiDetailsUrl: PropTypes.string.isRequired,
    };

    static defaultProps = {
        searchUrl: '/images/browse',
        locale: 'en',
        getCurrentLanguage: () => 'en',
    };

    constructor(props) {
        super(props);

        this.handleSearch = this.handleSearch.bind(this);
        this.handleOnSelect = this.handleOnSelect.bind(this);
        this.handleFetchImageDetails = this.handleFetchImageDetails.bind(this);

    }

    handleSearch(searchText, page) {
        return Axios.get(this.props.searchUrl, {
            params: {
                page: page,
                searchstring: typeof searchText !== 'undefined' ? searchText : null,
                language: this.props.getCurrentLanguage(),
            },
        })
            .then((response) => {
                return response.data;
            });
    }

    handleFetchImageDetails(imageId) {
        return Axios.get( this.props.apiDetailsUrl + '/' + imageId, {
            params: {
                language: this.props.getCurrentLanguage(),
            },
        })
            .then((response) => {
                return response.data;
            });
    }

    handleOnSelect(values) {
        this.props.onSelect(values);
    }

    render() {
        return (
            <ImageBrowserLayout
                onToggle={this.props.onToggle}
                onSearch={this.handleSearch}
                onFetch={this.handleFetchImageDetails}
                useImageText={this.props.intl.formatMessage({id: 'IMAGEBROWSER.USE'})}
                searchButtonTitle={this.props.intl.formatMessage({id: 'IMAGEBROWSER.SEARCH'})}
                locale={this.props.locale}
                searchPlaceholder={this.props.intl.formatMessage({id: 'IMAGEBROWSER.SEARCHPLACEHOLDER'})}
                onSelect={this.handleOnSelect}
                onCancel={this.props.onToggle}
            />
        );
    }
}

export default injectIntl(ImageBrowserContainer);
