import React, { Fragment } from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';
import { CardContainer } from '../QuestionCard';
import InfoBox from '../InfoBox';
import FooterBox from '../FooterBox';
import { DragDropContext, Droppable } from 'react-beautiful-dnd';
import { Card } from '../utils';

/**
 * @param {{cards: Card[]}} props
 * @return {Element}
 */
function MillionaireLayout(props) {
    const {
        cards,
        onChange,
        onDeleteCard,
        onReturnToOriginal,
        onGenerate,
        iconUrl,
        backButtonText,
        generateButtonText,
        infoText,
        processingForm,
        onChangeProcessing,
        editMode,
        handleDragEnd,
        isLockedPresentation,
        questionEditorConfig,
        answerEditorConfig
} = props;

    return (
        <Fragment>
            {editMode === false && !isLockedPresentation && (
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
            )}
            <DragDropContext onDragEnd={handleDragEnd}>
                <Droppable droppableId="questionSetDropZone">
                    {(provided) => (
                        <div
                            ref={provided.innerRef}
                            {...provided.droppableProps}
                        >
                            {cards.map((card, index) => (
                                <CardContainer
                                    key={'card_' + card.id}
                                    cardNumber={index + 1}
                                    onDeleteCard={onDeleteCard}
                                    card={card}
                                    collectData={onChange}
                                    showAddAnswerButton={false}
                                    questionEditorConfig={questionEditorConfig}
                                    answerEditorConfig={answerEditorConfig}
                                />
                            ))}
                            {provided.placeholder}
                        </div>
                    )}
                </Droppable>
            </DragDropContext>
            {editMode === false && !isLockedPresentation && (
                <FooterBox
                    generateButtonText={generateButtonText}
                    backButtonText={backButtonText}
                    onGenerateClick={onGenerate}
                    iconUrl={iconUrl}
                    onBackClick={onReturnToOriginal}
                    processingForm={processingForm}
                    onChangeProcessing={onChangeProcessing}
                />
            )}
        </Fragment>
    );
}

MillionaireLayout.propTypes = {
    cards: PropTypes.arrayOf(PropTypes.instanceOf(Card)),
    onChange: PropTypes.func,
    onGenerate: PropTypes.func,
    onReturnToOriginal: PropTypes.func,
    iconUrl: PropTypes.string,
    backButtonText: PropTypes.object,
    generateButtonText: PropTypes.object,
    infoText: PropTypes.node,
    processingForm: PropTypes.bool,
    onChangeProcessing: PropTypes.func,
    editMode: PropTypes.bool,
    isLockedPresentation: PropTypes.bool,
    questionEditorConfig: PropTypes.object,
    answerEditorConfig: PropTypes.object,
};

MillionaireLayout.defaultProps = {
    cards: [],
    backButtonText: <FormattedMessage id="MILLIONAIRE.GO_BACK_TO_ORIGINAL_QUESTION_SET" />,
    generateButtonText: <FormattedMessage id="MILLIONAIRE.GENERATE_GAME" />,
    infoText: '',
    processingForm: false,
    editMode: false,
    isLockedPresentation: false,
};

export default MillionaireLayout;
