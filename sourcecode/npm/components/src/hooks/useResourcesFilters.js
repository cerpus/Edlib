import React from 'react';
import useArray from './useArray';
import { useDebounce } from 'moment-hooks';

export default (contentFilter = 'myContent') => {
    const [searchInput, setSearchInput] = React.useState('');

    const tags = useArray();
    const sources = useArray();
    const contentTypes = useArray();
    const licenses = useArray();

    const debouncedSearchInput = useDebounce(searchInput, 500);

    const requestData = React.useMemo(
        () => ({
            contentFilter,
            contentTypes: contentTypes.value,
            licenses: licenses.value,
            sources: sources.value,
            keywords: tags.value.map((tag) => tag.value),
            searchString:
                debouncedSearchInput === '' ? null : debouncedSearchInput,
        }),
        [
            tags.value,
            sources.value,
            contentTypes.value,
            licenses.value,
            debouncedSearchInput,
            contentFilter,
        ]
    );

    const reset = React.useCallback(() => {
        tags.setValue([]);
        sources.setValue([]);
        contentTypes.setValue([]);
        licenses.setValue([]);
        setSearchInput('');
    }, [
        setSearchInput,
        tags.setValue,
        sources.setValue,
        contentTypes.setValue,
        licenses.setValue,
    ]);

    return {
        searchInput,
        debouncedSearchInput,
        setSearchInput,
        tags,
        sources,
        contentTypes,
        licenses,
        requestData,
        reset,
    };
};
