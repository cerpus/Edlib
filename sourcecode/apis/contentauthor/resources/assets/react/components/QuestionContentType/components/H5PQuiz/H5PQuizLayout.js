import React, { Fragment } from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage, FormattedHTMLMessage } from 'react-intl';
import { CardContainer } from '../QuestionCard';
import AddCard from '../QuestionCard/components/AddCard';
import InfoBox from '../InfoBox';
import FooterBox from '../FooterBox';

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
            {cards.map((card, index) => (
                <CardContainer
                    key={'card_' + card.id}
                    cardNumber={index + 1}
                    onDeleteCard={() => onDeleteCard(card.id)}
                    card={card}
                    collectData={onChange}
                />
            ))}
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
    infoText: PropTypes.object,
    processingForm: PropTypes.bool,
    onChangeProcessing: PropTypes.func,
};

H5PQuizLayout.defaultProps = {
    cards: [],
    backButtonText: <FormattedMessage id="H5PQUIZ.GO_BACK_TO_ORIGINAL_QUESTION_SET" />,
    generateButtonText: <FormattedMessage id="H5PQUIZ.GENERATE_QUIZ" />,
    infoText: <FormattedHTMLMessage id="H5PQUIZ.WE_HAVE_ADDED_SOME_WRONG_ALTERNATIVES" />,
    processingForm: false,
};

export default H5PQuizLayout;
