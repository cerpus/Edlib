import React from 'react';
import PropTypes from 'prop-types';
import Button from '@material-ui/core/Button';
import { makeStyles } from '@material-ui/core/styles';
import {useIntl} from "react-intl";

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
    const useStyles = makeStyles((theme) => ({
        largeButton: {
            width: '100%',
        },
    }));
    const classes = useStyles();
    const intl = useIntl();
    return (
        <div className="addCard">
            <Button
                aria-label={intl.formatMessage({ id: 'QUESTIONCONTAINER.ADD_LABEL' })}
                onClick={onClick}
                className={classes.largeButton}
            >
                {icon}
                <span style={styles.addNewQuestionContainer}>{label} </span>
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
