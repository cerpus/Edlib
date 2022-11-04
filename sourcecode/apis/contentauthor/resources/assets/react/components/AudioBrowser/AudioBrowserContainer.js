import React, { Component } from 'react';
import PropTypes from 'prop-types';
import Axios from '../../utils/axiosSetup';
import { injectIntl } from 'react-intl';
import AudioBrowserLayout from './AudioBrowserLayout';

class AudioBrowserContainer extends Component {
    static propTypes = {
        searchUrl: PropTypes.string,
        onSelect: PropTypes.func.isRequired,
        locale: PropTypes.string,
        onToggle: PropTypes.func,
        getCurrentLanguage: PropTypes.func,
    };

    static defaultProps = {
        searchUrl: '/audios/browse',
        locale: 'en',
        getCurrentLanguage: () => 'en',
    };

    constructor(props) {
        super(props);

        this.handleSearch = this.handleSearch.bind(this);
        this.handleOnSelect = this.handleOnSelect.bind(this);
        this.handleFetchAudioDetails = this.handleFetchAudioDetails.bind(this);
    }

    handleSearch(query) {
        const paramQuery = query || {};
        paramQuery.language = this.props.getCurrentLanguage();
        return Axios.get(this.props.searchUrl, {
            params: {
                query: paramQuery,
            },
        }).then((response) => {
            return response.data;
        });
    }

    handleOnSelect(values) {
        this.handleFetchAudioDetails(values.id)
            .then(data => this.props.onSelect(data));
    }

    handleFetchAudioDetails(audioId) {
        return Axios.get(this.props.searchUrl + '/' + audioId, {
            params: {
                language: this.props.getCurrentLanguage(),
            }
        })
            .then((response) => {
                return response.data;
            });
    }

    render() {
        const {
            intl,
            onToggle,
        } = this.props;
        return (
            <AudioBrowserLayout
                onToggle={onToggle}
                onSearch={this.handleSearch}
                onSelect={this.handleOnSelect}
                onCancel={onToggle}
                onFetch={this.handleFetchAudioDetails}
                translations={{
                    searchPlaceholder: intl.formatMessage({id: 'AUDIOBROWSER.SEARCHPLACEHOLDER'}),
                    searchButtonTitle: intl.formatMessage({id: 'AUDIOBROWSER.SEARCHBUTTONTITLE'}),
                    noResults: intl.formatMessage({id: 'AUDIOBROWSER.NORESULTS'}),
                    useAudio: intl.formatMessage({id: 'AUDIOBROWSER.USEAUDIO'}),
                }}
            />
        );
    }
}

export default injectIntl(AudioBrowserContainer);
