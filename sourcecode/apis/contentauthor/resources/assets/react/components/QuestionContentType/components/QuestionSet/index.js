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
    };

    static defaultProps = {
        cards: [],
        contentTypes: [],
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
        const answer = new Answer();
        answer.showToggle = true;
        card.question = new Question();
        card.answers = [answer];
        card.order = this.props.cards.length;
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
                onAddCard={this.handleAddCard}
                onPresentationChange={this.props.onPresentationChange}
                contentTypes={this.props.contentTypes}
                handleDragEnd={this.handleDragEnd}
            />
        );
    }
};
