import React, { Fragment } from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';
import { CardContainer } from '../QuestionCard';
import AddCard from '../QuestionCard/components/AddCard';
import InfoBox from '../InfoBox';
import FooterBox from '../FooterBox';
import { DragDropContext, Droppable } from 'react-beautiful-dnd';

function H5PQuizLayout(props) {
    const {
        cards,
        onChange,
        onDeleteCard,
        onAddCard,
        onReturnToOriginal,
        onGenerate,
        iconUrl,
        backButtonText,
        generateButtonText,
        infoText,
        processingForm,
        onChangeProcessing,
        handleDragEnd,
    } = props;

    return (
        <Fragment>
            <InfoBox
                infoText={infoText}
                generateButtonText={generateButtonText}
                backButtonText={backButtonText}
                onGenerateClick={onGenerate}
                iconUrl={iconUrl}
                onBackClick={onReturnToOriginal}
                processingForm={processingForm}
                onChangeProcessing={onChangeProcessing}
            />
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
                                    onDeleteCard={() => onDeleteCard(card.id)}
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
                />
            )}
            <FooterBox
                generateButtonText={generateButtonText}
                backButtonText={backButtonText}
                onGenerateClick={onGenerate}
                iconUrl={iconUrl}
                onBackClick={onReturnToOriginal}
                processingForm={processingForm}
                onChangeProcessing={onChangeProcessing}
            />
        </Fragment>
    );
}

H5PQuizLayout.propTypes = {
    cards: PropTypes.array,
    onDeleteCard: PropTypes.func,
    onChange: PropTypes.func,
    onAddCard: PropTypes.func,
    onGenerate: PropTypes.func,
    onReturnToOriginal: PropTypes.func,
    iconUrl: PropTypes.string,
    backButtonText: PropTypes.object,
    generateButtonText: PropTypes.object,
    infoText: PropTypes.node,
    processingForm: PropTypes.bool,
    onChangeProcessing: PropTypes.func,
};

H5PQuizLayout.defaultProps = {
    cards: [],
    backButtonText: <FormattedMessage id="H5PQUIZ.GO_BACK_TO_ORIGINAL_QUESTION_SET" />,
    generateButtonText: <FormattedMessage id="H5PQUIZ.GENERATE_QUIZ" />,
    infoText: '',
    processingForm: false,
};

export default H5PQuizLayout;
