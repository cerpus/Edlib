import Axios from '../../../utils/axiosSetup';
import h5pFieldTypes from '../h5pFieldTypes';

const arrayKey = '$index$';
// H5p parameter keys that should not be shown to user in script view
const fieldNamesToIgnore = ['computedSettings'];
const pathTypes = {
    ARRAY_INDEX: 'arrayIndex',
};

const getItemFilterType = (semantic) => {
    const isHeader = semantic.name.toLowerCase().includes('header');
    const isTitle = semantic.name.toLowerCase().includes('title');
    const isAlt = ['imageAltText', 'alt'].indexOf(semantic.name) !== -1;
    const isHover = semantic.label && semantic.label.toLowerCase().includes('hover');

    return {
        content: !isHeader && !isTitle && !isAlt && !isHover,
        header: isHeader,
        title: !isHover && isTitle,
        alt: isAlt,
        hover: isHover,
    };
};

const getPathsFromSementics = (
    sementic,
    fieldTypes,
    results = [],
    path = [],
    isParentList = false
) => {
    const currentPath = [...path, isParentList ? arrayKey : sementic.name];

    if (fieldNamesToIgnore.indexOf(sementic.name) !== -1) {
        return results;
    }

    if (
        fieldTypes.some(fieldType => {
            if (fieldType.type !== sementic.type) {
                return false;
            }

            if (fieldType.widgets && !fieldType.widgets.some(widget => widget === sementic.widget)) {
                return false;
            }

            return true;
        })
    ) {
        return [...results, {
            path: currentPath,
            type: sementic.type,
            widget: sementic.widget,
            label: sementic.label,
            filterTypes: getItemFilterType(sementic),
            editorSemantics: {
                enterMode: sementic.enterMode ?? 'div',
                font: sementic.font ?? {},
                tags: sementic.tags ?? null,
            },
        }];
    }

    if (sementic.type === 'group') {
        if (Object.keys(sementic.fields).length === 1) {
            currentPath.pop();
            return getPathsFromSementics(sementic.fields[0], fieldTypes, results, currentPath);
        }

        return sementic.fields.reduce(
            (results, field) =>
                getPathsFromSementics(field, fieldTypes, results, currentPath),
            results
        );
    }

    if (sementic.type === 'list') {
        return getPathsFromSementics(
            sementic.field,
            fieldTypes,
            results,
            currentPath,
            true
        );
    }

    return results;
};

const getValue = (parameters, path) =>
    path.reduce((value, key) => {
        if (!value) {
            return value;
        }

        let actualKey = key;

        if (key.type === pathTypes.ARRAY_INDEX) {
            actualKey = key.index;
        }

        return (value && value[actualKey]) || null;
    }, parameters);

const getActualValuesAndPaths = (
    parameters,
    currentPath,
    wholePath,
    atIndex = 0
) => {
    if (wholePath.length === 0) {
        return [];
    }

    if (atIndex >= wholePath.length) {
        return [
            {
                path: currentPath,
                value: getValue(parameters, currentPath),
            },
        ];
    }

    if (wholePath[atIndex] === arrayKey) {
        const value = getValue(parameters, currentPath);

        if (!Array.isArray(value)) {
            return [];
        }

        return value
            .map((arrayItem, index) =>
                getActualValuesAndPaths(
                    parameters,
                    [...currentPath, { type: pathTypes.ARRAY_INDEX, index }],
                    wholePath,
                    atIndex + 1
                )
            )
            .flat();
    }

    const newCurrentPath = [...currentPath, wholePath[atIndex]];

    const value = getValue(parameters, newCurrentPath);

    if (!value) {
        return [];
    }

    if (atIndex + 1 < wholePath.length) {
        return getActualValuesAndPaths(
            parameters,
            newCurrentPath,
            wholePath,
            atIndex + 1
        );
    }

    return [
        {
            path: newCurrentPath,
            value,
        },
    ];
};

let libraryCache = {};

const getTranslationJobs = async (parameters, libraryName, loadedLibraries) => {
    if (!libraryName) {
        return [];
    }

    if (loadedLibraries) {
        libraryCache = loadedLibraries;
    }

    let semantics = libraryCache[libraryName];
    if (!semantics) {
        semantics = (await Axios.get(`/v1/h5p-libraries/${libraryName}`)).data;
        libraryCache[libraryName] = semantics;
    }

    const pathsToTranslate = semantics.semantics
        .filter(
            sementic =>
                ['l10n', 'override'].indexOf(sementic.name) === -1 && !sementic.common
        )
        .map(sementic => getPathsFromSementics(sementic, [
            {
                type: 'text',
                widgets: ['textarea', 'html', undefined],
            },
            {
                type: h5pFieldTypes.LIBRARY,
            },
        ]))
        .flat();

    const translationJobs = [];

    for (const { path, type, widget, filterTypes, editorSemantics, label } of pathsToTranslate) {
        const actualValuesAndPaths = getActualValuesAndPaths(
            parameters.params,
            [],
            path
        );

        for (const { value, path } of actualValuesAndPaths) {
            if (!value) {
                continue;
            }

            if (type === h5pFieldTypes.LIBRARY) {
                const jobs = await getTranslationJobs(value, value.library);
                translationJobs.push(
                    ...jobs.map(job => ({
                        ...job,
                        path: [...path, 'params', ...job.path],
                    }))
                );
            } else {
                translationJobs.push({
                    path,
                    label,
                    originalValue: value,
                    type,
                    widget,
                    filterTypes,
                    editorSemantics,
                });
            }
        }
    }

    return translationJobs;
};

export default async (parameters, libraryName, loadedLibraries) => {
    const jobs = await getTranslationJobs(parameters, libraryName, loadedLibraries);

    const getPathParts = (path) => path.reduce((parts, path) => {
        const isArrayIndex = path.type === pathTypes.ARRAY_INDEX;

        if (isArrayIndex) {
            parts.push(path);
            parts.push([]);
            return parts;
        }

        if (parts.length === 0) {
            parts.push([]);
        }

        parts[parts.length - 1].push(path);

        return parts;
    }, []).filter((part) => part.length !== 0).map((part) => part.type === pathTypes.ARRAY_INDEX ? part : part.join('_'));

    const isArrayIndex = (part) => part.type === pathTypes.ARRAY_INDEX;

    let groupKey = null;
    return jobs.map(job => ({
        ...job,
        parts: getPathParts(job.path),
    })).sort((a, b) => {
        return a.parts.reduce((result, aPart, index) => {
            if (result !== 0) {
                return result;
            }

            const bPart = b.parts[index];

            if (!bPart) {
                return -1;
            }

            if (isArrayIndex(aPart) && !isArrayIndex(bPart)) {
                return -1;
            }

            if (!isArrayIndex(aPart) && isArrayIndex(bPart)) {
                return 1;
            }

            if (isArrayIndex(aPart) && isArrayIndex(bPart)) {
                return aPart.index - bPart.index;
            }

            return aPart.localeCompare(bPart);
        }, 0);
    }).map((job) => {
        if (!groupKey) {
            groupKey = job.parts[0];
        }

        let group = null;

        if (job.parts[0] === groupKey && job.parts[1] && job.parts[1].type === pathTypes.ARRAY_INDEX) {
            group = job.parts[1].index;
        }

        return { ...job, group };
    });
};
