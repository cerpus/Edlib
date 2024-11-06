import React, {Component} from 'react';
import PropTypes from 'prop-types';
import QuestionsetLayout from './QuestionsetLayout';
import {Card, Answer, Question, rerenderMathJax} from '../utils';

export default QuestionsetLayout;

export class QuestionsetContainer extends Component {
    static propTypes = {
        cards: PropTypes.array,
        onDeleteCard: PropTypes.func,
        onChange: PropTypes.func,
        onAddCard: PropTypes.func,
        onPresentationChange: PropTypes.func,
        contentTypes: PropTypes.array,
        handleDragEnd: PropTypes.func,
        canAddRemoveAnswer: PropTypes.bool,
        numberOfDefaultAnswers: PropTypes.number,
    };

    static defaultProps = {
        cards: [],
        contentTypes: [],
        canAddRemoveQuestion: true,
        canAddRemoveAnswer: true,
    };

    constructor(props) {
        super(props);

        this.handleAddCard = this.handleAddCard.bind(this);
    }

    componentDidMount() {
        rerenderMathJax();
    }

    handleAddCard() {
        const card = new Card();
        card.question = new Question();
        card.order = this.props.cards.length;
        card.canAddAnswer = this.props.canAddRemoveAnswer;
        card.canDelete = typeof this.props.onDeleteCard === 'function';

        for (let i = 0; i < this.props.numberOfDefaultAnswers; i++) {
            const answer = new Answer;
            answer.isCorrect = (i === 0);
            answer.showToggle = true;
            answer.canDelete = this.props.canAddRemoveAnswer;

            card.answers.push(answer);
        }

        const cards = [].concat(this.props.cards, [card]);
        this.props.onAddCard({
            cards: cards,
        });
    }

    render() {
        return (
            <QuestionsetLayout
                cards={this.props.cards}
                onChange={this.props.onChange}
                handleDeleteCard={this.props.onDeleteCard}
                onAddCard={this.props.onAddCard ? this.handleAddCard : null}
                onPresentationChange={this.props.onPresentationChange}
                contentTypes={this.props.contentTypes}
                handleDragEnd={this.props.handleDragEnd}
                numberOfDefaultAnswers={this.props.numberOfDefaultAnswers}
                canAddRemoveQuestion={this.props.canAddRemoveQuestion}
                canAddRemoveAnswer={this.props.canAddRemoveAnswer}
            />
        );
    }
};
