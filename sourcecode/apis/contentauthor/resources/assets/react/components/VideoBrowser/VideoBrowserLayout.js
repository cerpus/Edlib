import React from 'react';
import PropTypes from 'prop-types';
import NDLAVideo from '@ndla/video-search';
import Dialog from '../Dialog';

function VideoBrowserLayout({
    onCancel,
    onSearch,
    onSelect,
    locale,
    maxWidth,
    onToggle,
    translations,
}) {
    return (
        <Dialog
            maxWidth={maxWidth}
            onToggle={onToggle}
        >
            <NDLAVideo
                onVideoSelect={onSelect}
                searchVideos={(searchText, page) => onSearch(searchText, page)}
                translations={translations}
                onError={event => console.log(event)}
                locale={locale}
            />
        </Dialog>
    );
}

VideoBrowserLayout.propTypes = {
    onCancel: PropTypes.func,
    onSearch: PropTypes.func,
    onSelect: PropTypes.func.isRequired,
    locale: PropTypes.string.isRequired,
    onToggle: PropTypes.func,
    maxWidth: PropTypes.oneOfType([PropTypes.bool, PropTypes.string]),
    translations: PropTypes.object,
};

VideoBrowserLayout.defaultProps = {
    maxWidth: 'md',
    translations: {
        searchPlaceholder: 'Search videos',
        searchButtonTitle: 'Search',
        loadMoreVideos: 'Load more videos',
        noResults: 'Noe videos found',
        addVideo: 'Use video',
        previewVideo: 'Preview',
        publishedDate: 'Published date',
        duration: 'Duration',
        interactioncount: 'Views',
    },
};

export default VideoBrowserLayout;
