import React from 'react';
import { Button, FormGroup } from '@cerpus/ui';
import { useDebounce } from 'moment-hooks';
import { addStyles, EditableMathField } from 'react-mathquill';
import MathJax from '../../../../components/MathJax';
import useTranslation from '../../../../hooks/useTranslation';
import styled from 'styled-components';

addStyles();

const inputTypes = {
    MATH_QUILL: 'MATH_QUILL',
    LATEX: 'LATEX',
};

const MathInputWrapper = styled.div`
    border: 2px solid ${(props) => props.theme.colors.border};
    border-radius: 5px;
`;

const InputTypes = styled.div`
    border-bottom: ${(props) => props.theme.border};
    display: flex;
`;

const InputType = styled.div`
    padding: 10px;
    margin: 0 10px -1px;
    cursor: pointer;
    border-bottom: 2px solid transparent;

    ${(props) =>
        props.selected &&
        `
        border-bottom: 2px solid ${props.theme.colors.primary};
    `}
`;

const Input = styled.div`
    padding: 5px 5px;

    & > * {
        width: 100%;
        min-height: 100px;
        padding: 10px;
    }
    .mq-math-mode {
        border: 0;
        font-size: ${(props) => props.theme.rem(1)};
    }

    .mq-focused {
        box-shadow: none;
    }

    & > textarea {
        border: 0;
        outline: 0;
    }
`;

const Preview = styled.div`
    padding: 20px;
    border: 2px solid ${(props) => props.theme.colors.border};
`;

const Header = styled.div`
    margin-bottom: 5px;
`;

const MathEditor = ({
    currentValue,
    displayValue,
    setValue,
    valueType,
    onInsert,
}) => {
    const { t } = useTranslation();
    const [inputType, setInputType] = React.useState(inputTypes.MATH_QUILL);

    const [debouncedDisplayValue, debouncedValueType] = useDebounce(
        [displayValue, valueType],
        200
    );

    return (
        <>
            <h2>{t('Matematikkverktøyet')}</h2>
            <Header>{t('Skriv formelen')}:</Header>
            <MathInputWrapper>
                <InputTypes>
                    <InputType
                        selected={inputType === inputTypes.MATH_QUILL}
                        onClick={() => setInputType(inputTypes.MATH_QUILL)}
                    >
                        WYSIWYG
                    </InputType>
                    <InputType
                        selected={inputType === inputTypes.LATEX}
                        onClick={() => setInputType(inputTypes.LATEX)}
                    >
                        Latex
                    </InputType>
                </InputTypes>
                <Input>
                    {inputType === inputTypes.MATH_QUILL && (
                        <EditableMathField
                            latex={currentValue}
                            onChange={(mathField) => {
                                setValue(mathField.latex());
                            }}
                        />
                    )}
                    {inputType === inputTypes.LATEX && (
                        <textarea
                            style={{ width: '100%' }}
                            value={currentValue}
                            onChange={(e) => setValue(e.target.value)}
                        />
                    )}
                </Input>
            </MathInputWrapper>
            <Header style={{ marginTop: 20 }}>Slik vil formelen se ut:</Header>
            <Preview>
                {displayValue !== '' && (
                    <MathJax.Node
                        inline
                        type={debouncedValueType}
                        formula={debouncedDisplayValue}
                    />
                )}
            </Preview>
            <div style={{ marginTop: 20 }}>
                <Button
                    onClick={onInsert}
                    size="l"
                    style={{ minWidth: 150, textAlign: 'center' }}
                >
                    {t('Sett inn')}
                </Button>
            </div>
        </>
    );
};

export default MathEditor;
