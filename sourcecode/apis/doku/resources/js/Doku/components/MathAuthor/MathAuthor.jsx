import React from 'react';
import MathEditor from '../MathModal/MathEditor';

export default ({ onInsert, currentValue }) => {
    const [value, setValue] = React.useState('');
    const [displayValue, setDisplayValue] = React.useState('');
    const [valueType, setValueType] = React.useState('tex');

    React.useEffect(() => {
        setValue(
            currentValue.mathML || !currentValue.tex ? '' : currentValue.tex
        );
        setDisplayValue(
            currentValue.mathML
                ? currentValue.mathML
                : currentValue.tex
                    ? currentValue.tex
                    : ''
        );
        setValueType(currentValue.mathML ? 'mml' : 'tex');
    }, [currentValue]);

    return (
        <div style={{ padding: 15 }}>
            <MathEditor
                currentValue={value}
                onInsert={() => onInsert(value)}
                displayValue={displayValue}
                valueType={valueType}
                setValue={(value) => {
                    setValue(value);
                    setDisplayValue(value);
                    setValueType('tex');
                }}
            />
        </div>
    );
};
