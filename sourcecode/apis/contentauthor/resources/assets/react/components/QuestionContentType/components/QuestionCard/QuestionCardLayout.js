import './QuestionCard.scss';

import React, { Fragment } from 'react';
import PropTypes from 'prop-types';
import AddIcon from '@material-ui/icons/Add';
import AddCircleIcon from '@material-ui/icons/AddCircle';
import { FormattedMessage, useIntl } from 'react-intl';
import clsx from 'clsx';

import AnswerListComponent from './components/AnswerList';
import { Question as QuestionComponent } from './components/Question';
import Draggable from '../Draggable';
import { Answer, Card, Question } from '../utils';

/**
 * @param {{question: Question, answers: Answer[], card: Card}} props
 * @return {Element}
 */
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
        questionEditorConfig,
        answerEditorConfig,
    } = props;

    const { formatMessage }  = useIntl();
    let layout = (
        <Fragment>
            <div className="questionCard">
                <span className="cardNumber">{cardNumber.toString().padStart(2, ' ')}</span>
                <QuestionComponent
                    question={question}
                    collectQuestion={collectData}
                    text={question.text}
                    image={question.image}
                    useImage={card.useImage}
                    richText={question.richText}
                    editorConfig={questionEditorConfig}
                />
                <AnswerListComponent
                    collectAnswers={collectData}
                    answers={answers}
                    showAddAnswer={showAddAnswerButton}
                    className={clsx({'withDeleteBtn': card.canDelete && typeof deleteCard === 'function'})}
                    editorConfig={answerEditorConfig}
                />
                {card.canDelete && typeof deleteCard === 'function' &&  (
                    <button
                        className="deleteButton"
                        onClick={deleteCard}
                        aria-label={formatMessage({id:'QUESTIONCARD.DELETE_BUTTON_LABEL'})}
                    >
                        <AddIcon />
                    </button>
                )}
            </div>
            {addToSet && (
                <button
                    className={'selectQuestionButton' + (selected ? ' addedToList' : '')}
                    onClick={addToSet}
                >
                    <AddCircleIcon />
                    <FormattedMessage
                        id={selected ? 'QUESTIONCARD.ADDED_BUTTON_LABEL' : 'QUESTIONCARD.ADD_BUTTON_LABEL'}
                    />
                </button>
            )}
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
    question: PropTypes.instanceOf(Question),
    answers: PropTypes.arrayOf(PropTypes.instanceOf(Answer)),
    deleteCard: PropTypes.func,
    collectData: PropTypes.func,
    selected: PropTypes.bool,
    addToSet: PropTypes.func,
    isDraggable: PropTypes.bool,
    card: PropTypes.instanceOf(Card),
    showAddAnswerButton: PropTypes.bool,
    questionEditorConfig: PropTypes.object,
    answerEditorConfig: PropTypes.object,
};

export default QuestionCardLayout;
