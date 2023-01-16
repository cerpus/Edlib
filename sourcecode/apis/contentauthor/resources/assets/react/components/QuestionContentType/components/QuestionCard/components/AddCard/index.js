import React from 'react';
import PropTypes from 'prop-types';
import { Button } from '@material-ui/core';

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
        }
    };
    return (
        <div className="addCard">
            <span className="cardNumber">{cardNumber}</span>
            <div className="addCardContainer">
                <Button
                    onClick={onClick}
                    size="large"
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
