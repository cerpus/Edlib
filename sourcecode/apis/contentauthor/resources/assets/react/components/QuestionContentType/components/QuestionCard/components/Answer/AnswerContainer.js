import React, { PureComponent } from 'react';
import PropTypes from 'prop-types';
import { AnswerLayout } from './AnswerLayout';
import clsx from 'clsx';

export class AnswerContainer extends PureComponent {
    static propTypes = {
        id: PropTypes.string,
        answerText: PropTypes.string,
        isCorrect: PropTypes.bool,
        canDelete: PropTypes.bool,
        title: PropTypes.string,
        onAnswerChange: PropTypes.func,
        onDeleteAnswer: PropTypes.func,
        showToggle: PropTypes.bool,
        readonly: PropTypes.bool,
        placeholder: PropTypes.string,
        additionalClass: PropTypes.string,
        useImage: PropTypes.bool,
        richText: PropTypes.bool,
        image: PropTypes.object,
        editorConfig: PropTypes.shape({
            warningAtLength: PropTypes.number,
            warningMessage: PropTypes.node,
        }),
    };

    static defaultProps = {
        isCorrect: false,
        answerText: '',
        readOnly: false,
        useImage: false,
        richText: true,
        image: null,
    };

    constructor(props) {
        super(props);

        this.handleChangeText = this.handleChangeText.bind(this);
        this.handleChangeCorrect = this.handleChangeCorrect.bind(this);
        this.handleDeleteAnswer = this.handleDeleteAnswer.bind(this);
        this.handleImageChange = this.handleImageChange.bind(this);
    }

    handleChange(changes) {
        if ( this.props.readonly === false && this.props.onAnswerChange ) {
            this.props.onAnswerChange(changes, this.props.id);
        }
    }

    handleImageChange(image) {
        this.handleChange({ image: image });
    }

    handleChangeText(text, submit) {
        const changes = {
            readyForSubmit: submit,
        };
        if ( text !== null ) {
            changes.answerText = text;
        }
        this.handleChange(changes);
    }

    handleChangeCorrect(event, checked) {
        this.handleChange({ isCorrect: checked });
    }

    handleDeleteAnswer() {
        this.props.onDeleteAnswer(this.props.id);
    }

    render() {
        return (
            <AnswerLayout
                answerText={this.props.answerText}
                isCorrect={this.props.isCorrect}
                title={this.props.title}
                canDelete={this.props.canDelete && this.props.readonly === false && typeof this.props.onDeleteAnswer === 'function'}
                onAnswerChange={this.props.readonly === false ? this.handleChangeText : null}
                additionalClass={clsx(this.props.additionalClass, this.props.isCorrect ? 'correctAnswer' : 'wrongAnswer')}
                image={this.props.image !== null ? this.props.image : null}
                deleteAnswer={this.handleDeleteAnswer}
                onToggle={this.props.readonly === false ? this.handleChangeCorrect : null}
                showToggle={this.props.showToggle}
                placeHolder={this.props.placeholder}
                onImageChange={this.handleImageChange}
                useImage={this.props.useImage}
                richText={this.props.richText}
                {...this.props.editorConfig}
            />
        );
    }
}
