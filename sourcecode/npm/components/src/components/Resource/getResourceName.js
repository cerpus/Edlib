import _ from 'lodash';
import resourceTypes from '../../config/resourceTypes';

const getResourceName = (resource) => {
    if (
        resource.resourceType === resourceTypes.H5P &&
        resource.contentAuthorType
    ) {
        const formatedType = resource.contentAuthorType.toLowerCase();

        if (formatedType.startsWith('h5p.')) {
            return `H5P - ${_.startCase(
                resource.contentAuthorType.substring(4)
            )}`;
        } else if (formatedType === 'article') {
            return 'Article';
        } else if (formatedType === 'questionset') {
            return 'Question set';
        } else if (formatedType === 'link') {
            return 'Link';
        } else if (formatedType === 'game') {
            return 'Game';
        }
    }

    if (resource.resourceType === resourceTypes.URL) {
        return 'Link';
    }

    if (resource.resourceType === resourceTypes.DOKU) {
        return 'Edstep';
    }

    return 'Edlib resource';
};

export default getResourceName;
