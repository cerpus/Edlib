import React from 'react';
import PropTypes from 'prop-types';
import QuestionContentTypeLayout from './QuestionContentTypeLayout';
import { Answer, Card, Image, Question } from './components/utils';

class QuestionContentType extends React.Component {
    static defaultProps = {
        numberOfDefaultQuestions: 2,
        numberOfDefaultAnswers: 2,
        questionset: null,
        extQuestionSetData: null,
        onChange: null,
        contentTypes: [],
        editMode: false,
        canAddRemoveQuestion: true,
        canAddRemoveAnswer: true,
        lockedPresentation: null,
    };

    static propTypes = {
        numberOfDefaultQuestions: PropTypes.number,
        numberOfDefaultAnswers: PropTypes.number,
        questionset: PropTypes.object,
        extQuestionSetData: PropTypes.object,
        onChange: PropTypes.func,
        onSave: PropTypes.func,
        contentTypes: PropTypes.array,
        editMode: PropTypes.bool,
        canAddRemoveQuestion: PropTypes.bool,
        canAddRemoveAnswer: PropTypes.bool,
        lockedPresentation: PropTypes.string,
    };

    questionsetOriginalData = {};

    constructor(props) {
        super(props);

        this.state = this.init();
        this.copyDataToOriginal();

        this.props.onChange(this.state);

        this.handleOnchange = this.handleOnchange.bind(this);
        this.handleReturnToOriginal = this.handleReturnToOriginal.bind(this);
    }

    init() {
        const stateData = {
            title: '',
            cards: [],
            tags: [],
            selectedPresentation: this.props.lockedPresentation || '',
        };

        const {
            props: {
                questionset,
                extQuestionSetData,
                numberOfDefaultQuestions,
                numberOfDefaultAnswers,
                canAddRemoveQuestion,
                canAddRemoveAnswer,
            },
        } = this;

        if (questionset !== null) {
            stateData.title = this.getQuestionSetTitle(questionset);
            stateData.tags = this.getQuestionSetTags(questionset);
            stateData.cards = this.getQuestionSetQuestions(questionset);
        } else if (extQuestionSetData !== null) {
            stateData.title = this.getQuestionSetTitle(extQuestionSetData);
            stateData.tags = this.getQuestionSetTags(extQuestionSetData);
            stateData.cards = this.getQuestionSetQuestions(extQuestionSetData);
        } else {
            const cards = [];
            for (let i = 0; i < numberOfDefaultQuestions; i++) {
                const card = new Card();
                const answers = [];

                for (let j = 0; j < numberOfDefaultAnswers; j++) {
                    const answer = new Answer();

                    answer.isCorrect = (j === 0);
                    answer.showToggle = canAddRemoveAnswer;
                    answer.canDelete = canAddRemoveAnswer;

                    answers.push(answer);
                }

                card.question = new Question();
                card.answers = answers;
                card.canAddAnswer = canAddRemoveAnswer;
                card.order = i;
                card.canDelete = canAddRemoveQuestion;

                cards.push(card);
            }

            stateData.cards = cards;
        }

        return stateData;
    }

    getQuestionSetTitle(questionSet) {
        if (questionSet.hasOwnProperty('title') && typeof questionSet.title === 'string') {
            return questionSet.title;
        }

        return '';
    }

    getQuestionSetTags(questionSet) {
        if (questionSet.hasOwnProperty('tags') && Array.isArray(questionSet.tags)) {
            return [].concat(questionSet.tags);
        }

        return [];
    }

    getQuestionSetQuestions(questionSet) {
        let cards = [];
        if (questionSet.hasOwnProperty('questions')) {
            cards = questionSet.questions.map(question => {
                const questionData = new Question();
                questionData.text = question.text;

                if (question.hasOwnProperty('imageObject')) {
                    const image = new Image();
                    image.id = question.imageObject;
                    image.url = question.imageUrl;
                    questionData.image = image;
                }

                const card = new Card();

                if (question.hasOwnProperty('id')) {
                    card.id = question.id;
                }

                card.order = question.order;
                card.question = questionData;
                card.canDelete = true;

                card.answers = question.answers.map((answer, index) => {
                    const answerElement = new Answer;

                    if (answer.hasOwnProperty('id')) {
                        answerElement.id = answer.id;
                    }

                    answerElement.answerText = answer.text;
                    answerElement.order = answer.order;
                    answerElement.isCorrect = answer.correct;
                    answerElement.showToggle = true;
                    answerElement.canDelete = index > 0;
                    if (answer.hasOwnProperty('imageObject')) {
                        const image = new Image();
                        image.id = answer.imageObject;
                        image.url = answer.imageUrl;
                        answerElement.image = image;
                    }
                    return answerElement;
                });

                return card;
            });
        }

        return cards;
    }

    copyDataToOriginal() {
        const state = Object.assign({}, this.state);
        delete state.title;
        delete state.tags;
        state.selectedPresentation = '';
        state.cards = state.cards.map(card => card.clone());
        this.questionsetOriginalData = state;
    }

    handleReturnToOriginal() {
        this.handleOnchange(this.questionsetOriginalData);
    }

    handleOnchange(data) {
        this.setState(Object.assign({}, this.state, data), () => {
            if (this.state.selectedPresentation === '') {
                this.copyDataToOriginal();
            }
            if (this.props.onChange) {
                this.props.onChange(this.state);
            }
        });
    }

    render() {
        return (
            <QuestionContentTypeLayout
                onChange={this.handleOnchange}
                title={this.state.title}
                questions={this.state.cards}
                tags={this.state.tags}
                currentContainer={this.state.selectedPresentation}
                isLockedPresentation={this.props.lockedPresentation !== null}
                onReset={this.handleReturnToOriginal}
                contentTypes={this.props.contentTypes}
                editMode={this.props.editMode}
                onSave={this.props.onSave}
                numberOfDefaultAnswers={this.props.numberOfDefaultAnswers}
                canAddRemoveQuestion={this.props.canAddRemoveQuestion}
                canAddRemoveAnswer={this.props.canAddRemoveAnswer}
            />
        );
    }
}

export default QuestionContentType;
