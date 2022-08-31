import React, { Component } from 'react';
import PropTypes from 'prop-types';

import CardLayout from './QuestionCardLayout';
import {
    messagesEnGb as QuestionCardComponentMessagesEnGb,
    messagesNbNo as QuestionCardComponentMessagesNbNo,
    messagesNnNo as QuestionCardComponentMessagesNnNo,
} from './components';

import { messages as QuestionCardEnGb } from './language/en-gb';
import { messages as QuestionCardNbNo } from './language/nb-no';
import { messages as QuestionCardNnNo } from './language/nn-no';

class CardContainer extends Component {
    static propTypes = {
        cardNumber: PropTypes.number.isRequired,
        onDeleteCard: PropTypes.func,
        collectData: PropTypes.func,
        card: PropTypes.object,
        onAddToSet: PropTypes.func,
        isDraggable: PropTypes.bool,
        showAddAnswerButton: PropTypes.bool,
    };

    static defaultProps = {
        onDeleteCard: null,
        collectData: null,
        card: null,
        onAddToSet: null,
        isDraggable: true,
        showAddAnswerButton: true,
    };

    getDeleteCardHandler() {
        if (this.props.onDeleteCard) {
            return () => this.props.onDeleteCard(this.props.card.id);
        }

        return null;
    }

    getCollectDataHandler() {
        if (this.props.collectData) {
            return (data => this.props.collectData(data, this.props.card.id));
        }

        return null;
    }

    getAddToSetHandler() {
        if (this.props.onAddToSet) {
            return (() => this.props.onAddToSet(this.props.card.id));
        }

        return null;
    }

    render() {
        return (
            <CardLayout
                cardNumber={this.props.cardNumber}
                question={this.props.card.question}
                answers={this.props.card.answers}
                deleteCard={this.getDeleteCardHandler()}
                collectData={this.getCollectDataHandler()}
                addToSet={this.getAddToSetHandler()}
                selected={this.props.card.hasOwnProperty('selected') ? this.props.card.selected : false}
                isDraggable={this.props.isDraggable}
                card={this.props.card}
                showAddAnswerButton={this.props.showAddAnswerButton}
            />
        );
    }
}

const messagesEnGb = Object.assign({}, QuestionCardComponentMessagesEnGb, QuestionCardEnGb);
const messagesNbNo = Object.assign({}, QuestionCardComponentMessagesNbNo, QuestionCardNbNo);
const messagesNnNo = Object.assign({}, QuestionCardComponentMessagesNnNo, QuestionCardNnNo);

export {
    CardLayout as default,
    CardContainer,
    messagesEnGb,
    messagesNbNo,
    messagesNnNo,
};
