import {
    externalSystemNames,
    contentAuthorTypes,
} from '../config/resourceTypes';

export default (resource) => {
    if (resource.version.externalSystemName === externalSystemNames.URL) {
        return 'Url';
    }

    if (
        resource.version.externalSystemName ===
        externalSystemNames.CONTENT_AUTHOR
    ) {
        if (resource.version.contentType.startsWith(contentAuthorTypes.H5P)) {
            return 'H5P';
        }

        switch (resource.version.contentType) {
            case contentAuthorTypes.article:
                return 'Article';
            case contentAuthorTypes.game:
                return 'Spill';
            case contentAuthorTypes.questionset:
                return 'Question set';
            default:
                break;
        }
    }

    return 'Uvisst';
};
