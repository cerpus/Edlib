import resourceTypes, { h5pTypes } from '../constants/resourceTypes.js';
import resourceCapabilities from '../constants/resourceCapabilities.js';

export const getCapabilities = ({ resourceType, contentAuthorType }) => {
    let capabilities = [];

    if (resourceType !== resourceTypes.URL) {
        capabilities.push(resourceCapabilities.VERSION);
        capabilities.push(resourceCapabilities.EDIT);
    }

    if (
        resourceType !== resourceTypes.H5P ||
        contentAuthorType !== h5pTypes.questionset
    ) {
        capabilities.push(resourceCapabilities.VIEW);
    }

    return capabilities;
};
