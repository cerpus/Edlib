import React from 'react';
import PropTypes from 'prop-types';

import { AnswerContainer } from '../Answer';
import AddAnswer from '../AddAnswer';

const AnswerListLayout = props => {
    return (
        <div className="answerList">
            {props.answers.map(answer => {
                return (
                    <AnswerContainer
                        key={'answer_' + answer.id}
                        onAnswerChange={props.onAnswerChange}
                        onDeleteAnswer={props.deleteAnswer ? props.deleteAnswer : null}
                        text={answer.answerText}
                        {...answer}
                    />
                );
            })
            }
            {props.showAddAnswer === true && typeof props.addAnswer === 'function' && (
                <AddAnswer onClick={props.addAnswer} label={props.addAnswerLabel} />
            )}
        </div>
    );
};

AnswerListLayout.propTypes = {
    onAnswerChange: PropTypes.func,
    showAddAnswer: PropTypes.bool,
    addAnswerLabel: PropTypes.node,
    onToggle: PropTypes.func,
    deleteAnswer: PropTypes.func,
    answers: PropTypes.array,
    readonly: PropTypes.bool,
};

AnswerListLayout.defaultProps = {
    onAnswerChange: null,
    showAddAnswer: false,
    addAnswerLabel: '',
    onToggle: null,
    deleteAnswer: null,
    answers: [],
    readonly: false,
};

export default AnswerListLayout;
