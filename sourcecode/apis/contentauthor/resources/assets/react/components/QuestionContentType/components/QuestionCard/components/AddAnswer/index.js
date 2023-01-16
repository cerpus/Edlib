import React from 'react';
import PropTypes from 'prop-types';
import { Button, Divider } from '@material-ui/core';
import { AddCircle } from '@material-ui/icons';

const AddAnswer = props => {
    const {
        onClick,
        icon = <AddCircle>add_circle</AddCircle>,
        label,
    } = props;

    const styles = {
        divider: {
            width: '100%',
            flexShrink: 'unset',
        },
        button: {
            flexGrow: 2,
            marginRight: 5,
        },
        dividerContainer: {
            width: '100%',
            border: '1px solid #000',
            borderStyle: 'dotted',
            borderRadius: '5px',
            textAlign: 'center',
        },
    };

    return (
        <div className="addAnswerContainer">
            <div style={styles.dividerContainer}>
                <Button
                    onClick={onClick}
                    style={styles.button}
                    aria-label={label}
                >
                    {icon}
                </Button>
            </div>
        </div>
    );
};

AddAnswer.propTypes = {
    onClick: PropTypes.func.isRequired,
    icon: PropTypes.object,
    label: PropTypes.node,
};

export default AddAnswer;
