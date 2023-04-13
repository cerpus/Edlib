import React, { Component } from 'react';
import PropTypes from 'prop-types';
import MillionaireLayout from './MillionaireLayout';
import { Answer, Card, Image, Question, rerenderMathJax } from '../utils';
import Axios from '../../../../utils/axiosSetup';
import { FormattedMessage, injectIntl } from 'react-intl';
import { stripHTML } from '../../../../utils/Helper';

export default MillionaireLayout;

class MillionaireContainer extends Component {
    static defaultProps = {
        cards: [],
        tags: [],
        title: null,
        questionSearchUrl: '/v1/questionsets/search/questions',
        editMode: false,
    };

    static propTypes = {
        cards: PropTypes.arrayOf(PropTypes.instanceOf(Card)),
        handleDeleteCard: PropTypes.func,
        onChange: PropTypes.func,
        onReturnToOriginal: PropTypes.func,
        onBulkChange: PropTypes.func,
        onToggleDialog: PropTypes.func,
        onSave: PropTypes.func,
        tags: PropTypes.array,
        title: PropTypes.string,
        questionSearchUrl: PropTypes.string,
        editMode: PropTypes.bool,
        handleDragEnd: PropTypes.func,
    };

    REQUIRED_NUM_ALTERNATIVES = 4;
    REQUIRED_NUM_QUESTIONS = 15;

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

        const messages = [];
        const qCheck = this.checkQuestionCount();
        const altCheck = this.checkAlternativesCount();

        if (qCheck === -1 || altCheck.tooFew.length > 0) {
            if (qCheck === -1) {
                messages.push(<FormattedMessage key="TFQ" id="MILLIONAIRE.TOO_FEW_QUESTIONS" tagName="div" />);
            }
            if (altCheck.tooFew.length > 0) {
                messages.push(
                    <FormattedMessage
                        key="TFA"
                        id="MILLIONAIRE.TOO_FEW_ALTERNATIVES"
                        values={{
                            'questionList': altCheck.tooFew.join(', '),
                        }}
                        tagName="div"
                    />
                );
            }
            this.loadAlternativesFromServer();
        }
        if (qCheck === 1) {
            messages.push(<FormattedMessage key="TMQ" id="MILLIONAIRE.TOO_MANY_QUESTIONS" tagName="div" />);
        }
        if (altCheck.tooMany.length > 0) {
            messages.push(
                <FormattedMessage
                    key="TMA"
                    id="MILLIONAIRE.TOO_MANY_ALTERNATIVES"
                    values={{
                        'questionList': altCheck.tooMany.join(', '),
                    }}
                    tagName="div"
                />
            );
        }

        if (messages.length > 0) {
            messages.unshift(
                <FormattedMessage
                    key="RI"
                    id="MILLIONAIRE.REQUIREMENT_INFO"
                    values={{
                        'questionCount': this.REQUIRED_NUM_QUESTIONS,
                        'altCount': this.REQUIRED_NUM_ALTERNATIVES,
                    }}
                    tagName="div"
                />
            );
            this.setState({
                infoText: messages,
            });
        } else {
            this.setState({
                infoText: <FormattedMessage id="MILLIONAIRE.ALL_SET_AND_READY_TO_GO" />,
            });
        }

        this.props.onToggleDialog();
        rerenderMathJax();
    }

    checkQuestionCount() {
        return this.props.cards.length === this.REQUIRED_NUM_QUESTIONS ?
            0 :
            this.props.cards.length < this.REQUIRED_NUM_QUESTIONS ? -1 : 1;
    }

    checkAlternativesCount() {
        const tooFew = [];
        const tooMany = [];

        this.props.cards.forEach(question => {
            if (Array.isArray(question.answers)) {
                if (question.answers.length < this.REQUIRED_NUM_ALTERNATIVES) {
                    tooFew.push(question.order + 1);
                } else if (question.answers.length > this.REQUIRED_NUM_ALTERNATIVES) {
                    tooMany.push(question.order + 1);
                }
            } else {
                tooFew.push(question.order + 1);
            }
        });

        return {
            'tooFew': tooFew,
            'tooMany': tooMany,
        };
    }

    handleProcessing(isProcessing) {
        this.setState({
            isProcessing: isProcessing,
        });
    }

    addQuestions() {
        const {
            cards,
            intl,
        } = this.props;

        const {
            additionalAnswers,
            additionalQuestions,
        } = this.state;
        const millionaireCards = cards.length < this.REQUIRED_NUM_QUESTIONS ? [].concat(cards, additionalQuestions).splice(0, this.REQUIRED_NUM_QUESTIONS) : [].concat(cards);

        if (millionaireCards.length < this.REQUIRED_NUM_QUESTIONS) {
            for (let i = millionaireCards.length || 0; i < this.REQUIRED_NUM_QUESTIONS; i++) {
                millionaireCards.push(this.prepareCard());
            }
        }

        millionaireCards.forEach((card, index) => {
            if (!Array.isArray(card.answers) || card.answers.length < this.REQUIRED_NUM_ALTERNATIVES) {
                for (let i = card.answers.length || 0; i < this.REQUIRED_NUM_ALTERNATIVES; i++) {
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
            card.order = index;
            card.useImage = true;
        });


        this.props.onBulkChange({ cards: millionaireCards });
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
        for (let i = 0; i < this.REQUIRED_NUM_ALTERNATIVES; i++) {
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
        return answers.length < this.REQUIRED_NUM_ALTERNATIVES;
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
                iconUrl="/graphical/MillionaireIcon.png"
                onReturnToOriginal={this.props.onReturnToOriginal}
                onGenerate={this.handleOnGenerate}
                processingForm={this.state.isProcessing}
                onChangeProcessing={this.handleProcessing}
                infoText={this.state.infoText}
                editMode={this.props.editMode}
                onAddCard={this.handleAddCard}
                minimumNumberOfQuestions={this.REQUIRED_NUM_QUESTIONS}
                onDisplayAddAnswerButton={this.handleDisplayAddAnswerButton}
                handleDragEnd={this.props.handleDragEnd}
            />);
    }
}

MillionaireContainer = injectIntl(MillionaireContainer);
export { MillionaireContainer };
