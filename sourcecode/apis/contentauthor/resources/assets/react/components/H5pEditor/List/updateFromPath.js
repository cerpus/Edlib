const getActualKey = key => {
    if (typeof key === 'object') {
        if (key.type === 'arrayIndex') {
            return key.index;
        }

        return null;
    }

    return key;
};

export const expandPath = path =>
    path.reduce(
        (newPath, key) => [
            ...newPath,
            getActualKey(key),
        ],
        ['parameters', 'params']
    );

const updateFromPath = (obj, path, value, index = 0) => {
    if (index >= path.length) {
        return value;
    }

    const actualKey = getActualKey(path[index]);

    const newValue = updateFromPath(obj[actualKey], path, value, index + 1);

    if (Array.isArray(obj)) {
        return obj.map((obj, index) => (index === actualKey ? newValue : obj));
    }

    return {
        ...obj,
        [actualKey]: newValue,
    };
};

export default (parameters, path, value) =>
    updateFromPath(
        JSON.parse(JSON.stringify(parameters)),
        expandPath(path),
        value
    );
