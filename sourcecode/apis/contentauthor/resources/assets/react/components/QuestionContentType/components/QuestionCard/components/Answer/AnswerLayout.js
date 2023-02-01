import './Answer.scss';

import React from 'react';
import PropTypes from 'prop-types';
import Icon from '@material-ui/core/Icon';
import Input from '@material-ui/core/Input';
import { FormattedMessage } from 'react-intl';

import Toggle from '../Toggle';
import { ImageContainer } from '../Image';
import RichEditor from '../../../../../RichEditor';
import HtmlContainer from '../../../../../HtmlContainer/HtmlContainer';
import { useEditorSetupContext } from '../../../../../../contexts/EditorSetupContext';

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
    } = props;
    const { editorLanguage } = useEditorSetupContext();
    let inputType;

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
        <div className={'answerCard ' + additionalClass}>
            {title && (
                <div className="answerTitle">{title}</div>
            )}

            {canDelete === true && typeof deleteAnswer === 'function' && (
                <Icon
                    className="deleteButton"
                    onClick={deleteAnswer}
                >add</Icon>
            )}
            <div className="answerContainer">
                {inputType}
                {useImage === true && onImageChange && (
                    <ImageContainer
                        onChange={onImageChange}
                        readOnly={onImageChange === null}
                        image={image}
                    />
                )}
                {showToggle && onToggle && (
                    <Toggle
                        onToggle={onToggle}
                        isCorrect={isCorrect}
                        rightLabel={<FormattedMessage id="ANSWER.LABEL_CORRECT" />}
                        leftLabel={<FormattedMessage id="ANSWER.LABEL_WRONG" />}
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
};

export {
    AnswerLayout,
};
