import _ from 'lodash';
import resourceTypes from '../../config/resourceTypes';

const getResourceName = (resource) => {
    if (resource.contentTypeInfo) {
        return resource.contentTypeInfo.title;
    }

    if (resource.version.externalSystemName === 'contentauthor') {
        const formatedType = resource.version.contentType.toLowerCase();

        if (formatedType.startsWith('h5p.')) {
            return `H5P - ${_.startCase(
                resource.version.contentType.substring(4)
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
