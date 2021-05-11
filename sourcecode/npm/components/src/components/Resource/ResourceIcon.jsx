import React from 'react';
import resourceTypes from '../../config/resourceTypes';
import H5PIcon from '../Icons/H5P';
import FontAwesomeIcon from '../Icons/FontAwesome';
import MaterialIcon from '../Icons/Material';

const getIconType = (resourceVersion) => {
    if (resourceVersion.externalSystemName === 'contentauthor') {
        const formatedType = resourceVersion.contentType.toLowerCase();

        if (formatedType.startsWith('h5p.')) {
            return 'h5p';
        } else if (formatedType === 'article') {
            return 'article';
        } else if (formatedType === 'questionset') {
            return 'questionset';
        } else if (formatedType === 'link') {
            return 'link';
        } else if (formatedType === 'game') {
            return 'game';
        }
    }

    if (resourceVersion.externalSystemName === resourceTypes.URL) {
        return 'link';
    }

    return 'external';
};

const ResourceIcon = ({ resourceVersion, fontSizeRem = 1.5 }) => {
    const type = getIconType(resourceVersion);

    if (type === 'h5p') {
        return (
            <H5PIcon
                name={resourceVersion.contentType.substring('H5P.'.length)}
                fontSizeRem={fontSizeRem * 2}
            />
        );
    }

    if (type === 'article') {
        return <FontAwesomeIcon name="newspaper-o" fontSizeRem={fontSizeRem} />;
    }

    if (type === 'questionset') {
        return <MaterialIcon name="DoneAll" fontSizeRem={fontSizeRem} />;
    }

    if (type === 'link') {
        return <MaterialIcon name="Link" fontSizeRem={fontSizeRem} />;
    }

    if (type === 'game') {
        return <MaterialIcon name="VideogameAsset" fontSizeRem={fontSizeRem} />;
    }

    return <span />;
};

export default ResourceIcon;
