import React from 'react';
import PropTypes from 'prop-types';
import NDLAAudio from '@ndla/audio-search';
import Dialog from '../Dialog';

function AudioBrowserLayout({
    onCancel,
    onSearch,
    onSelect,
    maxWidth,
    onToggle,
    onFetch,
    queryObject,
    translations,
}) {
    return (
        <Dialog
            maxWidth={maxWidth}
            onBackdropClick={onCancel}
            onToggle={onToggle}
        >
            <NDLAAudio
                onError={event => console.log(event)}
                onAudioSelect={onSelect}
                searchAudios={onSearch}
                fetchAudio={onFetch}
                queryObject={queryObject}
                translations={translations}
            />
        </Dialog>
    );
}

AudioBrowserLayout.propTypes = {
    onCancel: PropTypes.func,
    onSearch: PropTypes.func,
    onSelect: PropTypes.func.isRequired,
    onToggle: PropTypes.func,
    onFetch: PropTypes.func,
    maxWidth: PropTypes.oneOfType([PropTypes.bool, PropTypes.string]),
    translations: PropTypes.object,
    queryObject: PropTypes.object,
};

AudioBrowserLayout.defaultProps = {
    maxWidth: 'md',
    translations: {
        searchPlaceholder: 'Search audio',
        searchButtonTitle: 'Search',
        noResults: 'No audios found',
        useAudio: 'Use audio',
    },
    queryObject: {
        query: '',
        page: 1,
        pageSize: 20,
        locale: 'no-nb',
    },
};

export default AudioBrowserLayout;
