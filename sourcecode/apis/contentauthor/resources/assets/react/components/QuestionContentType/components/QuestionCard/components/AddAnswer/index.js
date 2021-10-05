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
        },
    };

    return (
        <div className="addAnswerContainer">
            <div>
                <Button
                    onClick={onClick}
                    style={styles.button}
                >
                    {icon}
                    {label}
                </Button>
            </div>
            <div style={styles.dividerContainer}>
                <Divider
                    style={styles.divider}
                />
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
