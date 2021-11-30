import React, { Component } from 'react';
import PropTypes from 'prop-types';
import Axios from '../../utils/axiosSetup';
import { injectIntl, intlShape } from 'react-intl';
import VideoBrowserLayout from './VideoBrowserLayout';
import { getLicenseByNBTitle } from '@ndla/licenses';

class VideoBrowserContainer extends Component {
    static propTypes = {
        searchUrl: PropTypes.string,
        onSelect: PropTypes.func.isRequired,
        locale: PropTypes.string,
        useImageText: PropTypes.string,
        searchButtonText: PropTypes.string,
        searchPlaceholder: PropTypes.string,
        onToggle: PropTypes.func,
        intl: intlShape,
    };

    static defaultProps = {
        searchUrl: '/videos/browse',
        locale: 'en',
    };

    constructor(props) {
        super(props);

        this.handleSearch = this.handleSearch.bind(this);
        this.handleOnSelect = this.handleOnSelect.bind(this);
    }

    handleSearch(query, source) {
        return Axios.get(this.props.searchUrl, {
            params: {
                source: source,
                query: typeof query !== 'undefined' ? query : null,
            },
        }).then((response) => {
            return response.data;
        });
    }

    handleOnSelect(values) {
        let licenseInfo = null;
        if (values.hasOwnProperty('custom_fields') && values.custom_fields.hasOwnProperty('license')) {
            licenseInfo = getLicenseByNBTitle(values.custom_fields.license);
        }
        values.licenseInfo = licenseInfo;
        this.props.onSelect(values);
    }

    render() {
        const { intl } = this.props;
        return (
            <VideoBrowserLayout
                onToggle={this.props.onToggle}
                onSearch={this.handleSearch}
                locale={this.props.locale}
                onSelect={this.handleOnSelect}
                onCancel={this.props.onToggle}
                translations={{
                    searchPlaceholder: intl.formatMessage({ id: 'VIDEOBROWSER.SEARCHPLACEHOLDER' }),
                    searchButtonTitle: intl.formatMessage({ id: 'VIDEOBROWSER.SEARCHBUTTONTITLE' }),
                    loadMoreVideos: intl.formatMessage({ id: 'VIDEOBROWSER.LOADMOREVIDEOS' }),
                    noResults: intl.formatMessage({ id: 'VIDEOBROWSER.NORESULTS' }),
                    addVideo: intl.formatMessage({ id: 'VIDEOBROWSER.ADDVIDEO' }),
                    previewVideo: intl.formatMessage({ id: 'VIDEOBROWSER.PREVIEWVIDEO' }),
                    publishedDate: intl.formatMessage({ id: 'VIDEOBROWSER.PUBLISHEDDATE' }),
                    duration: intl.formatMessage({ id: 'VIDEOBROWSER.DURATION' }),
                    interactioncount: intl.formatMessage({ id: 'VIDEOBROWSER.INTERACTIONCOUNT' }),
                }}
            />
        );
    }
}

export default injectIntl(VideoBrowserContainer);
