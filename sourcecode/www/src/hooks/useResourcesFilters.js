import { useDebounce } from 'moment-hooks';
import React from 'react';
import useArray from './useArray';

const useResourcesFilters = (contentFilter = 'myContent') => {
    const [searchInput, setSearchInput] = React.useState('');

    const tags = useArray();
    const languages = useArray();
    const contentTypes = useArray([], (list, item) =>
        list.findIndex((listItem) => listItem.value === item.value)
    );
    const licenses = useArray([], (list, item) =>
        list.findIndex((listItem) => listItem.value === item.value)
    );

    const debouncedSearchInput = useDebounce(searchInput, 500);

    const requestData = React.useMemo(
        () => ({
            contentFilter,
            contentTypes: contentTypes.value.map((ct) => ct.value),
            keywords: tags.value.map((tag) => tag.value),
            languages: languages.value,
            licenses: licenses.value.map((ct) => ct.value),
            searchString:
                debouncedSearchInput === '' ? null : debouncedSearchInput,
        }),
        [
            tags.value,
            languages.value,
            contentTypes.value,
            licenses.value,
            debouncedSearchInput,
            contentFilter,
        ]
    );

    const reset = React.useCallback(() => {
        tags.setValue([]);
        languages.setValue([]);
        contentTypes.setValue([]);
        licenses.setValue([]);
        setSearchInput('');
    }, [setSearchInput, tags, languages, contentTypes, licenses]);

    return {
        contentTypes,
        debouncedSearchInput,
        languages,
        licenses,
        requestData,
        reset,
        searchInput,
        setSearchInput,
        tags,
    };
};

export default useResourcesFilters;
