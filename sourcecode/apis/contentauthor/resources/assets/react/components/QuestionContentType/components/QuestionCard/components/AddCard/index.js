import React from 'react';
import PropTypes from 'prop-types';
import Button from '@material-ui/core/Button';
import {useIntl} from "react-intl";

const AddCard = props => {
    const {
        onClick,
        label = 'Add question',
        cardNumber,
        icon,
    } = props;

    const { formatMessage }  = useIntl();
    return (
        <div className="addCard">
            <Button
                aria-label={formatMessage({ id: 'QUESTIONCONTAINER.ADD_LABEL' })}
                onClick={onClick}
            >
                {icon}
                <span className="addNewQuestionContainer">{label} </span>
            </Button>
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
