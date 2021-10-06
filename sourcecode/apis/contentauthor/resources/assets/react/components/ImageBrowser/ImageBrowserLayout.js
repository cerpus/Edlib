import React from 'react';
import PropTypes from 'prop-types';
import NDLAImage from '@ndla/image-search';
import Dialog from 'Dialog';

function ImageBrowserLayout({
    onCancel,
    onSearch,
    onFetch,
    onSelect,
    useImageText,
    locale,
    searchButtonTitle,
    searchPlaceholder,
    maxWidth,
    onToggle,
}) {
    return (
        <Dialog
            maxWidth={maxWidth}
            onBackdropClick={onCancel}
            onToggle={onToggle}
        >
            <NDLAImage
                onImageSelect={onSelect}
                searchImages={(searchText, page) => onSearch(searchText, page)}
                fetchImage={imageId => onFetch(imageId)}
                onError={event => console.log(event)}
                searchPlaceholder={searchPlaceholder}
                searchButtonTitle={searchButtonTitle}
                locale={locale}
                useImageTitle={useImageText}
            />
        </Dialog>
    );
}

ImageBrowserLayout.propTypes = {
    onCancel: PropTypes.func,
    onSearch: PropTypes.func,
    onFetch: PropTypes.func,
    onSelect: PropTypes.func.isRequired,
    useImageText: PropTypes.string.isRequired,
    locale: PropTypes.string.isRequired,
    searchButtonTitle: PropTypes.string.isRequired,
    searchPlaceholder: PropTypes.string,
    onToggle: PropTypes.func,
    maxWidth: PropTypes.oneOfType([PropTypes.bool, PropTypes.string]),
};

ImageBrowserLayout.defaultProps = {
    searchPlaceholder: '',
    maxWidth: 'md',
};

export default ImageBrowserLayout;
