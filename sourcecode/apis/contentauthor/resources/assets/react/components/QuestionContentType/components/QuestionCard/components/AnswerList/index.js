import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';

import AnswerListLayout from './AnswerListLayout';
import { Answer } from '../../../utils';

class AnswerList extends Component {
    static propTypes = {
        answers: PropTypes.array,
        collectAnswers: PropTypes.func,
        showAddAnswer: PropTypes.bool,
    };

    static defaultProps = {
        answers: [],
        showAddAnswer: true,
        collectAnswers: null,
    };

    constructor(props) {
        super(props);

        this.handleAddAnswer = this.handleAddAnswer.bind(this);
        this.handleAnswerChange = this.handleAnswerChange.bind(this);
        this.handleDeleteAnswer = this.handleDeleteAnswer.bind(this);
    }

    handleChange(values) {
        if (this.props.collectAnswers) {
            this.props.collectAnswers({ answers: values });
        }
    }

    handleAnswerChange(newValues, answerId) {
        const answers = this.props.answers.map(answer => {
            if ( answer.id === answerId ) {
                return Object.assign(answer, newValues);
            }
            return answer;
        });
        this.handleChange(answers);
    }

    handleAddAnswer() {
        const answer = new Answer();
        answer.showToggle = true;
        answer.isCorrect = false;
        answer.canDelete = true;

        this.handleChange(this.props.answers.concat([answer]));
    }

    handleDeleteAnswer(answerId) {
        if ( this.props.answers.length > 1) {
            this.handleChange(this.props.answers.filter(answer => answer.id !== answerId));
        }
    }

    render() {
        return (
            <AnswerListLayout
                answers={this.props.answers}
                onAnswerChange={this.props.collectAnswers ? this.handleAnswerChange : null}
                showAddAnswer={this.props.showAddAnswer}
                addAnswer={this.props.collectAnswers ? this.handleAddAnswer : null}
                addAnswerLabel={<FormattedMessage id="ANSWERLIST.ADD_BUTTON" />}
                deleteAnswer={this.props.collectAnswers ? this.handleDeleteAnswer : null}
            />
        );
    }
}

export default AnswerList;

export { messages as messagesEnGb } from './language/en-gb';
export { messages as messagesNbNo } from './language/nb-no';
export { messages as messagesNnNo } from './language/nn-no';
