import { resourceCapabilities } from '../config/resource';

export default (resource) => {
    return Object.values(resourceCapabilities).reduce((result, capability) => {
        result[capability] =
            resource.resourceCapabilities.indexOf(capability) !== -1;
        return result;
    }, {});
};
