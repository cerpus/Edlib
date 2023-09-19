import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { Answer, Card, Question, rerenderMathJax } from '../utils';
import { FormattedMessage, injectIntl } from 'react-intl';
import Axios from '../../../../utils/axiosSetup';
import H5PQuizLayout from './H5PQuizLayout';

class H5PQuizContainer extends Component {
    static defaultProps = {
        cards: [],
        minimumNumberOfAnswers: 4,
        tags: [],
        title: '',
    };

    static propTypes = {
        cards: PropTypes.arrayOf(PropTypes.instanceOf(Card)),
        minimumNumberOfAnswers: PropTypes.number,
        handleDeleteCard: PropTypes.func,
        onChange: PropTypes.func,
        onReturnToOriginal: PropTypes.func,
        onBulkChange: PropTypes.func,
        onSave: PropTypes.func,
        onToggleDialog: PropTypes.func,
        tags: PropTypes.array,
        title: PropTypes.string,
        handleDragEnd: PropTypes.func,
    };

    state = {
        additionalAnswers: [],
        isProcessing: false,
        infoText: '',
    };

    constructor(props) {
        super(props);

        this.handleAddCard = this.handleAddCard.bind(this);
        this.handleOnGenerate = this.handleOnGenerate.bind(this);
        this.handleProcessing = this.handleProcessing.bind(this);
    }

    componentDidMount() {
        if (this.needToLoadFromServer()) {
            this.setState({
                infoText: (
                    <FormattedMessage
                        id="H5PQUIZ.WE_HAVE_ADDED_SOME_WRONG_ALTERNATIVES"
                        values={{
                            b: chunks => <b>{chunks}</b>,
                            'minAnswers': this.props.minimumNumberOfAnswers,
                        }}
                    />
                ),
            });
            this.loadAlternativesFromServer();
        } else {
            this.setState({
                infoText: <FormattedMessage id="H5PQUIZ.ALL_SET_AND_READY_TO_GO" />,
            });
            this.props.onToggleDialog();
        }
        rerenderMathJax();
    }

    needToLoadFromServer() {
        return this.props.cards.filter(question => !Array.isArray(question.answers) || question.answers.length < this.props.minimumNumberOfAnswers)
            .length > 0;
    }

    handleProcessing(isProcessing) {
        this.setState({
            isProcessing: isProcessing,
        });
    }

    addAlternatives() {
        const answers = [].concat(this.state.additionalAnswers);
        const cards = this.props.cards.map(card => {
            if (!Array.isArray(card.answers) || card.answers.length < this.props.minimumNumberOfAnswers) {
                for (let i = card.answers.length || 0; i < this.props.minimumNumberOfAnswers; i++) {
                    let newAnswer = answers.pop();
                    if (newAnswer === undefined) {
                        newAnswer = new Answer();
                        newAnswer.answerText = '';
                        newAnswer.isCorrect = i === 0;
                        newAnswer.placeholder = this.props.intl.formatMessage({ id: 'H5PQUIZ.MISSING_TEXT' });
                    }
                    newAnswer.title = this.props.intl.formatMessage({ id: 'H5PQUIZ.ADDED_ALTERNATIVE' });
                    newAnswer.showToggle = false;
                    newAnswer.canDelete = true;
                    newAnswer.additionalClass = 'H5PQuizAddedAlternative';
                    card.answers = [].concat(card.answers, [newAnswer]);
                }
            }
            return card;
        });

        this.props.onBulkChange({ cards: cards });
        this.props.onToggleDialog();
    }

    handleLoadedAlternatives(alternatives) {
        if (Array.isArray(alternatives)) {
            const answers = alternatives.map(loadedAnswer => {
                const answer = new Answer();
                answer.externalId = loadedAnswer.id;
                answer.answerText = loadedAnswer.text;
                answer.isCorrect = loadedAnswer.isCorrect;
                return answer;
            });
            this.setState({
                additionalAnswers: answers,
            }, this.addAlternatives);
        } else {
            this.addAlternatives();
        }
    }

    handleAddCard() {
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
        card.useImage = false;
        const cards = [].concat(this.props.cards, [card]);
        this.props.onBulkChange({
            cards: cards,
        });
    }

    loadAlternativesFromServer() {
        Axios.get('/v1/questionsets/search/answers', {
            params: {
                onlyWrong: 1,
                randomize: 1,
                tags: this.props.tags,
                title: this.props.title,
            },
        })
            .then(response => this.handleLoadedAlternatives(response.data));
    }

    handleOnGenerate() {
        if (this.props.onSave) {
            this.props.onSave();
        }
    }

    render() {
        const cards = this.props.cards.map(card => {
            card.useImage = false;
            return card;
        });
        return (
            <H5PQuizLayout
                cards={cards}
                onDeleteCard={this.props.handleDeleteCard}
                onChange={this.props.onChange}
                onAddCard={this.handleAddCard}
                iconUrl="/graphical/QuizIcon.png"
                onReturnToOriginal={this.props.onReturnToOriginal}
                onGenerate={this.handleOnGenerate}
                processingForm={this.state.isProcessing}
                onChangeProcessing={this.handleProcessing}
                infoText={this.state.infoText}
                handleDragEnd={this.props.handleDragEnd}
            />);
    }
}

export default injectIntl(H5PQuizContainer, { withRef: true });
