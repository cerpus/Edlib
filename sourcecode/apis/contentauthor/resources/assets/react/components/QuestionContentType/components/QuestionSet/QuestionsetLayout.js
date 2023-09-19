import React, { Fragment } from 'react';
import PropTypes from 'prop-types';
import { CardContainer } from '../QuestionCard';
import AddCard from '../QuestionCard/components/AddCard';
import { FormattedMessage } from 'react-intl';
import { PresentationContainer } from '../Presentation';
import AddCircleIcon from '@material-ui/icons/AddCircle';
import { DragDropContext, Droppable } from 'react-beautiful-dnd';

function QuestionSetLayout(props) {
    const {
        handleDeleteCard,
        onChange,
        onAddCard,
        cards,
        onPresentationChange,
        contentTypes,
        handleDragEnd,
    } = props;
    return (
        <Fragment>
            <DragDropContext onDragEnd={handleDragEnd}>
                <Droppable droppableId="questionSetDropZone">
                    {(provided, snapshot) => (
                        <div
                            ref={provided.innerRef}
                            {...provided.droppableProps}
                        >
                            {cards.map((card, index) => (
                                <CardContainer
                                    key={'card_' + card.id}
                                    cardNumber={index + 1}
                                    onDeleteCard={handleDeleteCard}
                                    card={card}
                                    collectData={onChange}
                                />
                            ))}
                            {provided.placeholder}
                        </div>
                    )}
                </Droppable>
            </DragDropContext>
            {typeof onAddCard === 'function' && (
                <AddCard
                    onClick={onAddCard}
                    cardNumber={cards.length + 1}
                    label={<FormattedMessage id="QUESTIONCONTAINER.ADD_LABEL" />}
                    icon={<AddCircleIcon />}
                />
            )}
            <PresentationContainer
                onHandleChange={onPresentationChange}
                contentTypes={contentTypes}
            />
        </Fragment>
    );
}

QuestionSetLayout.propTypes = {
    cards: PropTypes.array,
    handleDeleteCard: PropTypes.func,
    onChange: PropTypes.func,
    onAddCard: PropTypes.func,
    onPresentationChange: PropTypes.func,
    contentTypes: PropTypes.array,
};

QuestionSetLayout.defaultProps = {
    cards: [],
};

export default QuestionSetLayout;
