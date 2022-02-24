import React from 'react';

const defaultFinder = (list, item) =>
    list.findIndex((listItem) => listItem === item);

const useArray = (defaultValue = [], finder = defaultFinder) => {
    const [value, setValue] = React.useState(defaultValue);

    const has = React.useCallback(
        (hasValue) => finder(value, hasValue) !== -1,
        [value, finder]
    );

    const push = React.useCallback(
        (addedValue) => setValue([...value, addedValue]),
        [value, setValue]
    );

    const removeIndex = React.useCallback(
        (index) => setValue([...value].filter((x, i) => i !== index)),
        [value, setValue]
    );

    const toggle = React.useCallback(
        (valueToToggle) => {
            let newValue = [...value];

            let index = finder(newValue, valueToToggle);
            if (index !== -1) {
                newValue = newValue.filter(
                    (_, itemIndex) => itemIndex !== index
                );
            } else {
                newValue.push(valueToToggle);
            }

            setValue(newValue);
        },
        [value, finder]
    );

    return React.useMemo(
        () => ({
            setValue,
            toggle,
            has,
            push,
            removeIndex,
            value,
        }),
        [has, toggle, push, removeIndex, value]
    );
};

export default useArray;
