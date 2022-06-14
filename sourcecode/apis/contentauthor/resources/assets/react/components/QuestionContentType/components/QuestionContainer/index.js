import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { injectIntl, intlShape } from 'react-intl';
import QuestionContainerLayout from './QuestionContainerLayout';
import { H5PQuizContainer } from '../H5PQuiz';
import { QuestionsetContainer } from '../QuestionSet';
import { Card, uniqueId } from '../utils';
import { MillionaireContainer } from '../Millionaire';

export default QuestionContainerLayout;
export { messages as messagesEnGb } from './language/en-gb';
export { messages as messagesNbNo } from './language/nb-no';

class QuestionContainer extends Component {
    static propTypes = {
        numberOfCards: PropTypes.number,
        onSaveQuestionSet: PropTypes.func,
        title: PropTypes.string,
        questions: PropTypes.array,
        onChange: PropTypes.func,
        onSave: PropTypes.func,
        tags: PropTypes.array,
        currentContainer: PropTypes.string,
        onResetToOriginal: PropTypes.func,
        minimumSecondsDisplaytime: PropTypes.number,
        contentTypes: PropTypes.array,
        editMode: PropTypes.bool,
        intl: intlShape,
    };

    static defaultProps = {
        numberOfCards: 2,
        onSaveQuestionSet: null,
        title: '',
        questions: [],
        onChange: null,
        tags: [],
        currentContainer: '',
        minimumSecondsDisplaytime: 2,
        contentTypes: [],
        editMode: false,
    };

    state = {
        displayDialog: false,
        contentTitle: '',
        contentIcon: null,
        title: '',
    };

    constructor(props) {
        super(props);

        this.handleDeleteCard = this.handleDeleteCard.bind(this);
        this.handleEditTitle = this.handleEditTitle.bind(this);
        this.handleCollectAnswersAndQuestions = this.handleCollectAnswersAndQuestions.bind(this);
        this.handleQuestionBankSelection = this.handleQuestionBankSelection.bind(this);
        this.handleTagsChange = this.handleTagsChange.bind(this);
        this.handleChange = this.handleChange.bind(this);
        this.handlePresentationSelect = this.handlePresentationSelect.bind(this);
        this.handleToggleDialog = this.handleToggleDialog.bind(this);
        this.handleDragEnd = this.handleDragEnd.bind(this);
    }

    handleCollectAnswersAndQuestions(data, id) {
        const cards = this.props.questions.map(card => {
            if (card.id === id) {
                return Object.assign(new Card(), card, data);
            }
            return card;
        });
        this.handleChange({ cards: cards });
    }

    handleChange(values) {
        if (this.props.onChange) {
            this.props.onChange(values);
        }
    }

    handleDeleteCard(cardId) {
        this.handleChange({ cards: this.props.questions.filter(card => card.id !== cardId) });
    }

    handleEditTitle(title, inEditMode) {
        if (inEditMode === true) {
            this.handleChange({ title: title });
        } else {
            this.setState({
                title: title,
            });
        }
    }

    handleQuestionBankSelection(selection) {
        const questions = this.props.questions
            .filter(question => {
                return question.question.text !== '' || question.answers.filter(answer => answer.answerText !== '').length > 0;
            })
            .concat(
                selection.map((question, index) => {
                    const q = question.clone();
                    q.order = (this.props.questions.length + index);
                    q.useImage = true;
                    q.externalId = q.id;
                    q.id = uniqueId();
                    q.answers.forEach(answer => {
                        answer.readonly = false;
                    });
                    return q;
                }));


        this.handleChange({ cards: questions });
    }

    handleTagsChange(tags) {
        this.handleChange({
            tags: tags,
        });
    }

    handlePresentationSelect(presentationProperties) {
        const {
            presentation,
            icon,
            title,
            text,
        } = presentationProperties;
        const start = new Date();
        start.setSeconds(start.getSeconds() + this.props.minimumSecondsDisplaytime);
        this.setState({
            displayDialog: true,
            until: start,
            contentIcon: icon,
            contentTitle: title,
            contentText: text,
        });

        this.props.onChange({
            selectedPresentation: presentation,
        });
    }

    handleToggleDialog() {
        const self = this;
        const startToggle = function () {
            if ( isReady() ) {
                clearInterval(interval);
                self.setState({
                    displayDialog: !self.state.displayDialog,
                    until: null,
                });
                return true;
            }
            return false;
        };

        const isReady = function () {
            const now = new Date();
            return Number(now) >= Number(self.state.until);
        };

        let interval;
        if ( startToggle() !== true ) {
            interval = setInterval(startToggle, 500);
        }
    }

    reorder(list, startIndex, endIndex) {
        const result = Array.from(list);
        const [removed] = result.splice(startIndex, 1);
        result.splice(endIndex, 0, removed);

        return result.map((question, index) => {
            question.order = index;

            return question;
        });
    }

    handleDragEnd(result) {
        if (!result.destination) {
            return;
        }

        const reorderedCards = this.reorder(this.props.questions, result.source.index, result.destination.index);
        this.props.onChange({
            cards: reorderedCards,
        });
    }

    getCards(current) {
        const cards = [];
        switch (current) {
            case 'H5P.QuestionSet':
                cards.push(
                    <H5PQuizContainer
                        cards={this.props.questions}
                        key="h5pquiz"
                        handleDeleteCard={this.handleDeleteCard}
                        onChange={this.handleCollectAnswersAndQuestions}
                        onReturnToOriginal={this.props.onResetToOriginal}
                        onBulkChange={this.handleChange}
                        onToggleDialog={this.handleToggleDialog}
                        tags={this.props.tags}
                        onSave={this.props.onSave}
                    />);
                break;
            case 'CERPUS.MILLIONAIRE':
                cards.push(
                    <MillionaireContainer
                        cards={this.props.questions}
                        key="millionaire"
                        handleDeleteCard={this.handleDeleteCard}
                        onChange={this.handleCollectAnswersAndQuestions}
                        onReturnToOriginal={this.props.onResetToOriginal}
                        onBulkChange={this.handleChange}
                        onToggleDialog={this.handleToggleDialog}
                        tags={this.props.tags}
                        editMode={this.props.editMode}
                        onSave={this.props.onSave}
                    />);
                break;
            default:
                cards.push(
                    <QuestionsetContainer
                        cards={this.props.questions}
                        key="questionset"
                        onChange={this.handleCollectAnswersAndQuestions}
                        onDeleteCard={this.handleDeleteCard}
                        onAddCard={this.handleChange}
                        onPresentationChange={this.handlePresentationSelect}
                        contentTypes={this.props.contentTypes}
                    />
                );
        }

        return cards;
    }

    render() {
        return (
            <QuestionContainerLayout
                onAddCard={this.handleAddCard}
                cards={this.props.questions}
                cardsComponents={this.getCards(this.props.currentContainer)}
                onTitleChange={this.handleEditTitle}
                title={this.props.title}
                onQuestionBankSelect={null} // this.handleQuestionBankSelection
                tags={this.props.tags}
                onTagsChange={this.handleTagsChange}
                displayDialog={this.state.displayDialog}
                loadingIcon={this.state.contentIcon}
                loadingText={this.state.contentText}
                loadingTitle={this.state.contentTitle}
                editMode={this.props.editMode}
                handleDragEnd={this.handleDragEnd}
                searchTitle={this.state.title}
                placeholder={this.props.intl.formatMessage({ id: 'QUESTIONCONTAINER.TITLE_PLACEHOLDER' })}
            />
        );
    }
}

QuestionContainer = injectIntl(QuestionContainer);
export { QuestionContainer };
