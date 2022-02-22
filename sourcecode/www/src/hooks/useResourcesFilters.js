import React from 'react';
import useArray from './useArray';
import { useDebounce } from 'moment-hooks';

export default (contentFilter = 'myContent') => {
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
            licenses: licenses.value.map((ct) => ct.value),
            languages: languages.value,
            keywords: tags.value.map((tag) => tag.value),
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
    }, [
        setSearchInput,
        tags.setValue,
        languages.setValue,
        contentTypes.setValue,
        licenses.setValue,
    ]);

    return {
        searchInput,
        debouncedSearchInput,
        setSearchInput,
        tags,
        languages,
        contentTypes,
        licenses,
        requestData,
        reset,
    };
};
