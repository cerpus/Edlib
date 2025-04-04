import './Answer.scss';

import React from 'react';
import PropTypes from 'prop-types';
import Input from '@material-ui/core/Input';
import { useIntl } from 'react-intl';

import Toggle from '../Toggle';
import { ImageContainer } from '../Image';
import RichEditor from '../../../../../RichEditor';
import HtmlContainer from '../../../../../HtmlContainer/HtmlContainer';
import { useEditorSetupContext } from '../../../../../../contexts/EditorSetupContext';
import AddIcon from '@material-ui/icons/Add';
import clsx from 'clsx';

const AnswerLayout = props => {
    const {
        onToggle,
        isCorrect,
        onAnswerChange,
        showToggle,
        additionalClass,
        answerText,
        deleteAnswer,
        canDelete,
        placeHolder,
        title,
        image,
        onImageChange,
        useImage,
        maxRows,
        multiline,
        richText,
        warningAtLength,
        warningMessage,
    } = props;
    const { editorLanguage } = useEditorSetupContext();
    let inputType;
    const { formatMessage }  = useIntl();

    if ( onAnswerChange ) {
        if ( richText ) {
            inputType = (
                <RichEditor
                    value={answerText}
                    onChange={onAnswerChange}
                    language={editorLanguage}
                />
            );
        } else {
            inputType = (
                <Input
                    name="text"
                    placeholder={placeHolder}
                    onChange={event => onAnswerChange(event.currentTarget.value)}
                    value={answerText}
                    className="answerText"
                    fullWidth={true}
                    multiline={multiline}
                    maxRows={maxRows}
                />
            );
        }
    } else {
        inputType = (
            <HtmlContainer
                className="displayText"
                html={answerText}
                stripTags={false}
                firstParagraphFix={true}
                compactParagraphs={true}
            />
        );
    }

    return (
        <div className={clsx('answerCard', additionalClass)}>
            {title && (
                <div className="answerTitle">{title}</div>
            )}

            {canDelete === true && typeof deleteAnswer === 'function' && (
                 <button
                 className="deleteButton"
                 onClick={deleteAnswer}
                 aria-label={formatMessage({ id: 'QUESTIONCARD.DELETE_BUTTON_LABEL' })}
             >
                 <AddIcon />
             </button>
            )}
            <div className={clsx(
                'answerContainer',
                {
                    'withDeleteBtn': canDelete === true && typeof deleteAnswer === 'function',
                }
            )}>
                {inputType}
                {(onAnswerChange && warningMessage && warningAtLength && answerText.length > warningAtLength) ?
                    warningMessage:
                    null
                }
                {useImage === true && onImageChange && (
                    <ImageContainer
                        onChange={onImageChange}
                        readOnly={false}
                        image={image}
                    />
                )}
                {showToggle && onToggle && (
                    <Toggle
                        onToggle={onToggle}
                        isCorrect={isCorrect}
                        rightLabel={formatMessage({id: 'ANSWER.LABEL_CORRECT'})}
                        leftLabel={formatMessage({id: 'ANSWER.LABEL_WRONG'})}
                    />
                )}
            </div>
        </div>
    );
};

AnswerLayout.propTypes = {
    onToggle: PropTypes.func,
    onAnswerChange: PropTypes.func,
    onImageChange: PropTypes.func,
    isCorrect: PropTypes.bool,
    showToggle: PropTypes.bool,
    additionalClass: PropTypes.string,
    answerText: PropTypes.string,
    deleteAnswer: PropTypes.func,
    canDelete: PropTypes.bool,
    placeHolder: PropTypes.string,
    title: PropTypes.string,
    image: PropTypes.object,
    id: PropTypes.string,
    useImage: PropTypes.bool,
    maxRows: PropTypes.number,
    multiline: PropTypes.bool,
    richText: PropTypes.bool,
    warningAtLength: PropTypes.number,
    warningMessage: PropTypes.node,
};

AnswerLayout.defaultProps = {
    onToggle: null,
    onAnswerChange: null,
    isCorrect: true,
    showToggle: false,
    additionalClass: '',
    answerText: '',
    deleteAnswer: null,
    canDelete: false,
    placeholder: '',
    title: null,
    image: null,
    id: null,
    useImage: false,
    maxRows: 4,
    multiline: true,
    richText: true,
    warningAtLength: null,
    warningMessage: null,
};

export {
    AnswerLayout,
};
