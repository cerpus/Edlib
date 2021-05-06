import resourceTypes, { h5pTypes } from '../config/resourceTypes';

export default (resource) => {
    if (resource.resourceType === resourceTypes.URL) {
        return 'Url';
    }

    if (resource.resourceType === resourceTypes.H5P) {
        switch (resource.h5pType) {
            case h5pTypes.article:
                return 'Artical';
            case h5pTypes.game:
                return 'Spill';
            case h5pTypes.questionset:
                return 'Question set';
            case h5pTypes.H5P:
            default:
                return 'H5P';
        }
    }

    return 'Uvisst';
};
