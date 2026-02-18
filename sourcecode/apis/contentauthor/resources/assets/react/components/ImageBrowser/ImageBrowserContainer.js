import React, { Component } from 'react';
import PropTypes from 'prop-types';
import Axios from '../../utils/axiosSetup';
import ImageBrowserLayout from './ImageBrowserLayout';
import { injectIntl } from 'react-intl';

class ImageBrowserContainer extends Component {
    static propTypes = {
        searchUrl: PropTypes.string.isRequired,
        onSelect: PropTypes.func.isRequired,
        locale: PropTypes.string,
        onToggle: PropTypes.func,
        getCurrentLanguage: PropTypes.func,
        detailsUrl: PropTypes.string.isRequired,
        searchParams: PropTypes.object,
    };

    static defaultProps = {
        locale: 'en',
        getCurrentLanguage: () => 'en',
        searchParams: {},
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
                ...this.props.searchParams,
                page: page,
                query: typeof searchText !== 'undefined' ? searchText : null,
                language: this.props.getCurrentLanguage(),
            },
        })
            .then((response) => {
                return response.data;
            });
    }

    handleFetchImageDetails(imageId) {
        return Axios.get( this.props.detailsUrl + '/' + imageId, {
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
