import React from 'react';
import PropTypes from 'prop-types';
import Button from '@material-ui/core/Button';

const AddCard = props => {
    const {
        onClick,
        label = 'Add question',
        cardNumber,
        icon,
    } = props;
    const styles = {
        addNewQuestionContainer: {
            padding: '8px',
            textTransform: 'none',
            fontSize: '1.6rem',
            fontWeight: '400 !important',
        }
    };
    return (
        <div className="addCard" onClick={onClick}>
            <span className="cardNumber">{cardNumber}</span>
            <div className="addCardContainer">
                <Button
                    size="large"
                    aria-label="Add question"
                >
                    {icon}
                    <span style={styles.addNewQuestionContainer}>{label} </span>
                </Button>
            </div>
        </div>
    );
};

AddCard.propTypes = {
    onClick: PropTypes.func,
    cardNumber: PropTypes.number,
    label: PropTypes.node,
    icon: PropTypes.object,
};

export default AddCard;
