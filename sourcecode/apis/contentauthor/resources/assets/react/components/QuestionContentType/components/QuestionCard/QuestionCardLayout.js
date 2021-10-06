import './QuestionCard.scss';

import React, { Fragment } from 'react';
import PropTypes from 'prop-types';
import { Add as AddIcon, AddCircle as AddCircleIcon } from '@material-ui/icons';
import { FormattedMessage } from 'react-intl';

import AnswerListComponent from './components/AnswerList';
import { Question } from './components/Question';
import Draggable from '../Draggable';

function QuestionCardLayout(props) {
    const {
        cardNumber,
        question,
        answers,
        deleteCard,
        collectData,
        selected,
        addToSet,
        isDraggable,
        card,
        showAddAnswerButton = true,
    } = props;

    let layout = (
        <Fragment>
            <div className="questionCard">
                <span className="cardNumber">{cardNumber}</span>
                <Question
                    collectQuestion={collectData}
                    text={question.text}
                    image={question.image}
                    useImage={card.useImage}
                    richText={question.richText}
                />
                <AnswerListComponent
                    collectAnswers={collectData}
                    answers={answers}
                    showAddAnswer={showAddAnswerButton}
                />
                {typeof deleteCard === 'function' && (
                    <AddIcon
                        className="deleteButton"
                        onClick={deleteCard}
                    />
                )}
            </div>
            {addToSet &&
            <button
                className={'selectQuestionButton' + (selected ? ' addedToList' : '')}
                onClick={addToSet}
            >
                <AddCircleIcon />
                <FormattedMessage
                    id={selected ? 'QUESTIONCARD.ADDED_BUTTON_LABEL' : 'QUESTIONCARD.ADD_BUTTON_LABEL'}
                />
            </button>
            }
        </Fragment>
    );

    if (isDraggable === true) {
        layout = (
            <Draggable
                dragKey={'question#' + card.id}
                index={card.order}
            >
                {layout}
            </Draggable>
        );
    }
    return layout;
}

QuestionCardLayout.propTypes = {
    cardNumber: PropTypes.number,
    question: PropTypes.object,
    answers: PropTypes.array,
    deleteCard: PropTypes.func,
    collectData: PropTypes.func,
    selected: PropTypes.bool,
    addToSet: PropTypes.func,
    isDraggable: PropTypes.bool,
    card: PropTypes.object,
    showAddAnswerButton: PropTypes.bool,
};

export default QuestionCardLayout;
