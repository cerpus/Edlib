import React from 'react';
import { Modal } from '@cerpus/ui';
import MathEditor from './MathEditor';

const MathModal = ({ isOpen, onClose, onInsert, currentValue }) => {
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
        <Modal isOpen={isOpen} onClose={onClose} displayCloseButton={true}>
            <div style={{ padding: 10 }}>
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
        </Modal>
    );
};

export default MathModal;
