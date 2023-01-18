import React, { Fragment } from 'react';
import PropTypes from 'prop-types';
import { CardContainer } from '../QuestionCard';
import AddCard from '../QuestionCard/components/AddCard';
import { FormattedMessage } from 'react-intl';
import { PresentationContainer } from '../Presentation';
import AddCircleIcon from '@material-ui/icons/AddCircle';

function QuestionSetLayout(props) {
    const {
        handleDeleteCard,
        onChange,
        onAddCard,
        cards,
        onPresentationChange,
        contentTypes,
    } = props;
    return (
        <Fragment>
            {cards.map((card, index) => (
                <CardContainer
                    key={'card_' + card.id}
                    cardNumber={index + 1}
                    onDeleteCard={handleDeleteCard}
                    card={card}
                    collectData={onChange}
                />)
            )}
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
