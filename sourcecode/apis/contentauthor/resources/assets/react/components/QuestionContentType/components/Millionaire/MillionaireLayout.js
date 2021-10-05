import React, { Fragment } from 'react';
import PropTypes from 'prop-types';
import { FormattedHTMLMessage, FormattedMessage } from 'react-intl';
import { CardContainer } from '../QuestionCard';
import InfoBox from '../InfoBox';
import FooterBox from '../FooterBox';
import AddCard from '../QuestionCard/components/AddCard';

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
        minimumNumberOfQuestions,
        onAddCard,
        onDisplayAddAnswerButton,
    } = props;

    return (
        <Fragment>
            {editMode === false && (
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
            {cards.map((card, index) => (
                <CardContainer
                    key={'card_' + card.id}
                    cardNumber={index + 1}
                    onDeleteCard={() => onDeleteCard(card.id)}
                    card={card}
                    collectData={onChange}
                    showAddAnswerButton={onDisplayAddAnswerButton(card.answers)}
                />))}
            {cards.length < minimumNumberOfQuestions && typeof onAddCard === 'function' && (
                <AddCard
                    onClick={onAddCard}
                    cardNumber={cards.length + 1}
                    label={<FormattedMessage id="QUESTIONCONTAINER.ADD_LABEL" />}
                />
            )}
            {editMode === false && (
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
    cards: PropTypes.array,
    onDeleteCard: PropTypes.func,
    onChange: PropTypes.func,
    onAddCard: PropTypes.func,
    onGenerate: PropTypes.func,
    onReturnToOriginal: PropTypes.func,
    iconUrl: PropTypes.string,
    backButtonText: PropTypes.object,
    generateButtonText: PropTypes.object,
    infoText: PropTypes.oneOfType([PropTypes.object, PropTypes.string]),
    processingForm: PropTypes.bool,
    onChangeProcessing: PropTypes.func,
    editMode: PropTypes.bool,
    minimumNumberOfQuestions: PropTypes.number,
    onDisplayAddAnswerButton: PropTypes.func,
};

MillionaireLayout.defaultProps = {
    cards: [],
    backButtonText: <FormattedMessage id="MILLIONAIRE.GO_BACK_TO_ORIGINAL_QUESTION_SET" />,
    generateButtonText: <FormattedMessage id="MILLIONAIRE.GENERATE_GAME" />,
    infoText: <FormattedHTMLMessage id="MILLIONAIRE.WE_HAVE_ADDED_SOME_WRONG_ALTERNATIVES" />,
    processingForm: false,
    editMode: false,
};

export default MillionaireLayout;
