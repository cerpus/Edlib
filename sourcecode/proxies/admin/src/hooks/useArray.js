import { useCallback, useMemo, useState } from 'react';

const useArray = (initialArray) => {
    const [value, setValue] = useState(initialArray);
    const push = useCallback(
        (a) => {
            setValue((v) => [...v, ...(Array.isArray(a) ? a : [a])]);
        },
        []
    );

    const removeIndex = useCallback(
        (index) => setValue((v) => {
            const copy = v.slice();
            copy.splice(index, 1);
            return copy;
        }),
        []
    );

    const actions = useMemo(
        () => ({
            setValue,
            push,
            removeIndex,
        }),
        [push, removeIndex]
    );

    return [value, actions];
};

export default useArray;
