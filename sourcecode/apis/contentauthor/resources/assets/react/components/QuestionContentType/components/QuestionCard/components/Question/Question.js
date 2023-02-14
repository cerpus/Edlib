import './Question.scss';

import React from 'react';
import PropTypes from 'prop-types';
import { injectIntl } from 'react-intl';
import TextField from '@material-ui/core/TextField';
import { ImageContainer } from '../Image';
import RichEditor from '../../../../../RichEditor';
import HtmlContainer from '../../../../../HtmlContainer/HtmlContainer';
import { useEditorSetupContext } from '../../../../../../contexts/EditorSetupContext';

function Question(props) {
    const {
        question,
        onChange,
        placeholder,
        image,
        useImage,
        maxRows,
        multiline,
        intl,
        richText,
    } = props;
    const { editorLanguage } = useEditorSetupContext();
    let questionText = null;

    if (onChange) {
        if (richText) {
            questionText = (
                <RichEditor
                    value={question}
                    onChange={(data, submit) => onChange(data, 'text', submit)}
                    language={editorLanguage}
                />
            );
        } else {
            questionText = (
                <TextField
                    name="question"
                    value={question}
                    placeholder={intl.formatMessage({ id: placeholder })}
                    onChange={event => onChange(event.currentTarget.value, 'text')}
                    fullWidth={true}
                    multiline={multiline}
                    maxRows={maxRows}
                />);
        }
    } else {
        questionText = (
            <HtmlContainer
                className="questionContainerDisplay"
                html={question}
                stripTags={false}
                firstParagraphFix={true}
                compactParagraphs={true}
            />
        );
    }

    return (
        <div className="questionContainer">
            {questionText}
            {useImage === true && (
                <ImageContainer
                    onChange={onChange}
                    readOnly={onChange === null}
                    image={image}
                />)}
        </div>
    );
}

Question.propTypes = {
    onChange: PropTypes.func,
    question: PropTypes.string,
    placeholder: PropTypes.string,
    image: PropTypes.object,
    useImage: PropTypes.bool,
    maxRows: PropTypes.number,
    multiline: PropTypes.bool,
    richText: PropTypes.bool,
};

Question.defaultProps = {
    useImage: false,
    maxRows: 4,
    multiline: true,
    placeholder: 'QUESTIONCARD.QUESTION_PLACEHODLER',
    richText: false,
};

export default injectIntl(Question);
