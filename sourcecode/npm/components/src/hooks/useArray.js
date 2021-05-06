import React from 'react';

export default (defaultValue = []) => {
    const [value, setValue] = React.useState(defaultValue);

    const has = React.useCallback(
        (hasValue) => value.indexOf(hasValue) !== -1,
        [value]
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

            if (has(valueToToggle)) {
                newValue = newValue.filter(
                    (itemValue) => itemValue !== valueToToggle
                );
            } else {
                newValue.push(valueToToggle);
            }

            setValue(newValue);
        },
        [value]
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
