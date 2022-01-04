import React from 'react';
import PropTypes from 'prop-types';
import Axios from 'axios';
import { FormattedMessage } from 'react-intl';

import { QuestionBankBrowserDialog, QuestionBankBrowserLayout } from './';
import { Answer, Card, Image, Question, rerenderMathJax } from '../utils';
import { CardContainer } from '../QuestionCard';
import { Debounce } from '../../../../utils/utils';

class QuestionBankBrowser extends React.Component {
    static propTypes = {
        onSelect: PropTypes.func,
        cards: PropTypes.array,
        title: PropTypes.string,
        tags: PropTypes.array,
        status: PropTypes.string,
    };

    static defaultProps = {
        onSelect: null,
        cards: [],
        title: '',
        tags: '',
        status: 'success',
    };

    constructor(props) {
        super(props);

        this.state = {
            questionSets: [],
            previewOpen: false,
            selectedQuestionSet: null,
            status: 'success',
        };

        this.handlePreview = this.handlePreview.bind(this);
        this.handlePreviewClose = this.handlePreviewClose.bind(this);
        this.handleAddToSet = this.handleAddToSet.bind(this);
        this.handleAddAllToSet = this.handleAddAllToSet.bind(this);
    }

    componentDidMount() {
        const {
            title,
            tags,
        } = this.props;

        if (title.length > 0 || tags.length > 0) {
            this.getQuestionSets({
                title: title,
                tags: tags,
            });
        }
    }

    componentWillReceiveProps(nextProps) {
        if (this.state.selectedQuestionSet !== null) {
            const selected = Object.assign({}, this.state.selectedQuestionSet);
            nextProps.cards.forEach((card) => {
                const sIndex = selected.cards.findIndex((sCard) => {
                    return sCard.id === card.externalId;
                });
                if (sIndex !== -1) {
                    selected.cards[sIndex].selected = true;
                }
            });

            this.setState({
                selectedQuestionSet: selected,
            });
        }
        if (nextProps.title === '' && nextProps.tags.length === 0) {
            this.setState({
                questionSets: [],
            });
        } else if ((this.props.title !== nextProps.title) ||
            (nextProps.tags.length !== this.props.tags.length)) {
            this.getQuestionSets({ title: nextProps.title, tags: nextProps.tags });
        }
    }

    isQuestionsSelected(userSet, questionId) {
        return userSet.findIndex((card) => {
            return (card.hasOwnProperty('externalId') ? card.externalId === questionId : false);
        }) !== -1;
    }

    getQuestionSets = Debounce(search => {
        this.setState({
            status: 'fetching',
        });

        Axios.get('/v1/questionsets', {
            params: search,
            timeout: 10000,
        })
            .then((response) => {
                this.setState({
                    questionSets: response.data,
                    status: 'success',
                });
            })
            .catch((error) => {
                this.setState({
                    status: 'error',
                });
                console.log(error);
            });
    }, 5);

    getQuestions(setId) {
        Axios.get('/v1/questionsets/' + setId + '/questions')
            .then((response) => {
                this.transformQuestionData(setId, response.data);
                rerenderMathJax();
            })
            .catch((error) => {
                console.log(error);
            });
    }

    transformQuestionData(setId, questionData) {
        const selectedSet = this.state.questionSets.find(set => {
            return set.id === setId;
        });

        const cards = questionData.map((question, index) => {
            const card = new Card();
            card.id = question.id;
            card.externalId = (question.externalId || null);

            const questionData = new Question();
            questionData.text = question.text;
            if (question.hasOwnProperty('imageObject') && question.imageObject !== null) {
                const image = new Image();
                image.id = question.imageObject;
                image.url = question.hasOwnProperty('imageUrl') ? question.imageUrl : null;
                questionData.image = image;
            } else {
                card.useImage = false;
            }

            card.question = questionData;
            card.order = index;
            card.selected = this.isQuestionsSelected(this.props.cards, question.id);
            card.readonly = true;
            card.answers = question.answers.map((answer) => {
                const cardAnswer = new Answer();
                cardAnswer.id = answer.id;
                cardAnswer.answerText = answer.text;
                cardAnswer.isCorrect = answer.isCorrect;
                cardAnswer.canDelete = true;
                cardAnswer.showToggle = true;
                cardAnswer.readonly = true;
                if (answer.hasOwnProperty('imageObject') && answer.imageObject !== null) {
                    const image = new Image();
                    image.id = answer.imageObject;
                    image.url = answer.hasOwnProperty('imageUrl') ? answer.imageUrl : null;
                    cardAnswer.image = image;
                }

                return cardAnswer;
            });
            return card;
        });

        this.setState({
            selectedQuestionSet: Object.assign({}, selectedSet, { cards: cards }),
        });
    }

    handlePreview(setId) {
        this.setState({
            previewOpen: true,
        });
        this.getQuestions(setId);
    }

    handlePreviewClose() {
        rerenderMathJax();
        this.setState({
            previewOpen: false,
            selectedQuestionSet: null,
        });
    }

    handleAddToSet(questionId) {
        if (this.props.onSelect) {
            this.props.onSelect([
                this.state.selectedQuestionSet.cards.find(card => {
                    return card.id === questionId;
                }),
            ]);
        }
    }

    handleAddAllToSet() {
        if (this.props.onSelect) {
            this.props.onSelect(this.state.selectedQuestionSet.cards);
        }
    }

    renderPreview() {
        if (this.state.selectedQuestionSet && this.state.previewOpen) {
            const title = (
                <div>
                    {this.state.selectedQuestionSet.title}
                    <button className="questionBankAddAll" onClick={this.handleAddAllToSet}>
                        <i className="material-icons">add_circle</i>
                        <FormattedMessage id="QUESTIONBANKBROWSER.ADD_ALL_LABEL" />
                    </button>
                </div>
            );
            return (
                <QuestionBankBrowserDialog
                    title={title}
                    open={this.state.previewOpen}
                    onRequestClose={this.handlePreviewClose}
                >
                    {this.state.selectedQuestionSet.cards.map((card, index) => {
                        return (
                            <CardContainer
                                key={'card_' + card.id}
                                cardNumber={index + 1}
                                card={card}
                                onAddToSet={this.handleAddToSet}
                                isDraggable={false}
                            />
                        );
                    })}
                </QuestionBankBrowserDialog>
            );
        }

        return null;
    }

    render() {
        return (
            <QuestionBankBrowserLayout
                questionSets={this.state.questionSets}
                onSelect={this.handlePreview}
                previewDialog={this.renderPreview()}
                status={this.state.status}
            />
        );
    }
}

export default QuestionBankBrowser;
