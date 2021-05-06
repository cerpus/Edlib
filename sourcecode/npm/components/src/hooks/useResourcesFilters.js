import React from 'react';
import useArray from './useArray';
import { useDebounce } from 'moment-hooks';

export default (contentFilter = 'myContent') => {
    const [searchInput, setSearchInput] = React.useState('');

    const tags = useArray();
    const sources = useArray();
    const h5pTypes = useArray();
    const licenses = useArray();

    const debouncedSearchInput = useDebounce(searchInput, 500);

    const requestData = React.useMemo(
        () => ({
            contentFilter,
            h5pTypes: h5pTypes.value,
            licenses: licenses.value,
            sources: sources.value,
            keywords: tags.value.map((tag) => tag.value),
            searchString:
                debouncedSearchInput === '' ? null : debouncedSearchInput,
        }),
        [
            tags.value,
            sources.value,
            h5pTypes.value,
            licenses.value,
            debouncedSearchInput,
            contentFilter,
        ]
    );

    const reset = React.useCallback(() => {
        tags.setValue([]);
        sources.setValue([]);
        h5pTypes.setValue([]);
        licenses.setValue([]);
        setSearchInput('');
    }, [
        setSearchInput,
        tags.setValue,
        sources.setValue,
        h5pTypes.setValue,
        licenses.setValue,
    ]);

    return {
        searchInput,
        debouncedSearchInput,
        setSearchInput,
        tags,
        sources,
        h5pTypes,
        licenses,
        requestData,
        reset,
    };
};
