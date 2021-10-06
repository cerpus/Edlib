import { expandPath } from './updateFromPath';

export default (parameters, path) =>
    expandPath(path).reduce(
        (value, key) => (value ? value[key] : value),
        parameters
    );
