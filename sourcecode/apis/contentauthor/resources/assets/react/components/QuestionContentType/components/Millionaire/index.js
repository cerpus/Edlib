import React, { Component } from 'react';
import PropTypes from 'prop-types';
import MillionaireLayout from './MillionaireLayout';
import { Answer, Card, Image, Question, rerenderMathJax } from '../utils';
import Axios from '../../../../utils/axiosSetup';
import { FormattedMessage, injectIntl, intlShape } from 'react-intl';
import { stripHTML } from '../../../../utils/Helper';

export default MillionaireLayout;

export { messages as messagesEnGb } from './language/en-gb';
export { messages as messagesNbNo } from './language/nb-no';

class MillionaireContainer extends Component {
    static defaultProps = {
        cards: [],
        minimumNumberOfAnswers: 4,
        minimumNumberOfQuestions: 15,
        tags: [],
        title: null,
        questionSearchUrl: '/v1/questionsets/search/questions',
        editMode: false,
    };

    static propTypes = {
        cards: PropTypes.arrayOf(PropTypes.instanceOf(Card)),
        minimumNumberOfAnswers: PropTypes.number,
        minimumNumberOfQuestions: PropTypes.number,
        handleDeleteCard: PropTypes.func,
        onChange: PropTypes.func,
        onReturnToOriginal: PropTypes.func,
        onBulkChange: PropTypes.func,
        onToggleDialog: PropTypes.func,
        onSave: PropTypes.func,
        tags: PropTypes.array,
        title: PropTypes.string,
        intl: intlShape,
        questionSearchUrl: PropTypes.string,
        editMode: PropTypes.bool,
    };

    state = {
        additionalQuestions: [],
        additionalAnswers: [],
        isProcessing: false,
        infoText: '',
    };

    constructor(props) {
        super(props);

        this.handleAddCard = this.handleAddCard.bind(this);
        this.handleOnGenerate = this.handleOnGenerate.bind(this);
        this.handleProcessing = this.handleProcessing.bind(this);
        this.handleDisplayAddAnswerButton = this.handleDisplayAddAnswerButton.bind(this);
    }

    componentDidMount() {
        if (this.props.editMode === true) {
            return;
        }
        if (this.needToLoadFromServer()) {
            this.loadAlternativesFromServer();
        } else {
            this.setState({
                infoText: <FormattedMessage id="MILLIONAIRE.ALL_SET_AND_READY_TO_GO" />,
            });
            this.props.onToggleDialog();
        }
        rerenderMathJax();
    }

    needToLoadFromServer() {
        return this.props.cards.length < this.props.minimumNumberOfQuestions ||
            this.props.cards.filter(question => !Array.isArray(question.answers) || question.answers.length < this.props.minimumNumberOfAnswers).length > 0;
    }


    handleProcessing(isProcessing) {
        this.setState({
            isProcessing: isProcessing,
        });
    }

    addQuestions() {
        const {
            cards,
            minimumNumberOfQuestions,
            minimumNumberOfAnswers,
            intl,
        } = this.props;

        const {
            additionalAnswers,
            additionalQuestions,
        } = this.state;
        const millionaireCards = cards.length < minimumNumberOfQuestions ? [].concat(cards, additionalQuestions).splice(0, minimumNumberOfQuestions) : [].concat(cards);

        if (millionaireCards.length < minimumNumberOfQuestions) {
            for (let i = millionaireCards.length || 0; i < minimumNumberOfQuestions; i++) {
                millionaireCards.push(this.prepareCard());
            }
        }

        millionaireCards.forEach(card => {
            if (!Array.isArray(card.answers) || card.answers.length < minimumNumberOfAnswers) {
                for (let i = card.answers.length || 0; i < minimumNumberOfAnswers; i++) {
                    let newAnswer = additionalAnswers.pop();
                    if (newAnswer === undefined) {
                        newAnswer = new Answer();
                        newAnswer.answerText = '';
                        newAnswer.isCorrect = i === 0;
                        newAnswer.placeholder = intl.formatMessage({ id: 'MILLIONAIRE.MISSING_TEXT' });
                    }
                    newAnswer.title = intl.formatMessage({ id: 'MILLIONAIRE.ADDED_ALTERNATIVE' });
                    newAnswer.showToggle = true;
                    newAnswer.canDelete = true;
                    newAnswer.additionalClass = 'H5PQuizAddedAlternative';
                    card.answers = [].concat(card.answers, [newAnswer]);
                }
            }
            card.useImage = true;
        });


        this.props.onBulkChange({ cards: millionaireCards });
        this.props.onToggleDialog();
    }

    handleLoadedQuestions(loadedQuestions) {
        if (Array.isArray(loadedQuestions)) {
            const questions = loadedQuestions.map(loadedQuestion => {
                const question = new Question();
                question.text = loadedQuestion.text;
                if (loadedQuestion.imageObject !== null) {
                    const image = new Image();
                    image.id = loadedQuestion.imageObject;
                    image.url = loadedQuestion.imageUrl;
                    question.image = image;
                }

                const answers = loadedQuestion.answers.map(loadedAnswer => {
                    const answer = new Answer();
                    answer.externalId = loadedAnswer.id;
                    answer.answerText = loadedAnswer.text;
                    answer.isCorrect = loadedAnswer.isCorrect;
                    answer.showToggle = true;
                    answer.canDelete = true;
                    return answer;
                });

                const card = new Card();
                card.externalId = loadedQuestion.id;
                card.question = question;
                card.answers = answers;
                return card;
            });
            this.setState({
                additionalQuestions: questions,
                additionalAnswers: this.state.additionalAnswers.concat(questions.map(question => question.answers).reduce((accumulator, current) => accumulator.concat(current), [])),
            }, this.addQuestions);
        } else {
            this.addQuestions();
        }
    }

    prepareCard() {
        const answers = [];
        for (let i = 0; i < this.props.minimumNumberOfAnswers; i++) {
            const answer = new Answer();
            answer.answerText = '';
            answer.isCorrect = i === 0;
            answer.showToggle = i !== 0;
            answer.canDelete = i !== 0;
            answers.push(answer);
        }

        const card = new Card();
        card.question = new Question();
        card.answers = answers;
        card.order = this.props.cards.length;
        return card;
    }

    handleAddCard() {
        this.props.onBulkChange({
            cards: [].concat(this.props.cards, [this.prepareCard()]),
        });
    }

    loadAlternativesFromServer() {
        Axios.get(this.props.questionSearchUrl, {
            params: {
                randomize: 1,
                tags: this.props.tags,
                title: this.props.title,
            },
        })
            .then(response => this.handleLoadedQuestions(response.data));
    }

    handleOnGenerate() {
        if (this.props.onSave) {
            this.props.onSave();
        }
    }

    handleDisplayAddAnswerButton(answers) {
        return answers.length < this.props.minimumNumberOfAnswers;
    }

    render() {
        const cards = this.props.cards.map(card => {
            card.useImage = true;
            card.question.richText = false;
            card.question.text = stripHTML(card.question.text, false);
            card.answers.forEach(answer => {
                answer.richText = false;
                answer.answerText = stripHTML(answer.answerText, false);
                return answer;
            });
            return card;
        });

        return (
            <MillionaireLayout
                cards={cards}
                onDeleteCard={this.props.handleDeleteCard}
                onChange={this.props.onChange}
                iconUrl="/h5pstorage/libraries/H5P.QuestionSet-1.13/icon.svg"
                onReturnToOriginal={this.props.onReturnToOriginal}
                onGenerate={this.handleOnGenerate}
                processingForm={this.state.isProcessing}
                onChangeProcessing={this.handleProcessing}
                infoText={this.state.infoText}
                editMode={this.props.editMode}
                onAddCard={this.handleAddCard}
                minimumNumberOfQuestions={this.props.minimumNumberOfQuestions}
                onDisplayAddAnswerButton={this.handleDisplayAddAnswerButton}
            />);
    }
}

MillionaireContainer = injectIntl(MillionaireContainer);
export { MillionaireContainer };
