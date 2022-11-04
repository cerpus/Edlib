import React, { Component } from 'react';
import PropTypes from 'prop-types';
import Axios from '../../utils/axiosSetup';
import ImageBrowserLayout from './ImageBrowserLayout';

class ImageBrowserContainer extends Component {
    static propTypes = {
        searchUrl: PropTypes.string,
        onSelect: PropTypes.func.isRequired,
        locale: PropTypes.string,
        useImageText: PropTypes.string,
        searchButtonText: PropTypes.string,
        searchPlaceholder: PropTypes.string,
        onToggle: PropTypes.func,
        getCurrentLanguage: PropTypes.func,
    };

    static defaultProps = {
        searchUrl: '/images/browse',
        locale: 'en',
        useImageText: 'Use',
        searchButtonText: 'Search',
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
        return Axios.get( this.props.searchUrl + '/' + imageId, {
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
                useImageText={this.props.useImageText}
                searchButtonTitle={this.props.searchButtonText}
                locale={this.props.locale}
                searchPlaceholder={this.props.searchPlaceholder}
                onSelect={this.handleOnSelect}
                onCancel={this.props.onToggle}
            />
        );
    }
}

export default ImageBrowserContainer;
