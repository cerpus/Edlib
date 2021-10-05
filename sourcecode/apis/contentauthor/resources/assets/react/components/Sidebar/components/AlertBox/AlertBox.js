import React from 'react';
import { FormActions, useForm } from 'contexts/FormContext';
import { List, ListItemText } from '@material-ui/core';
import { Alert } from '@cerpus/ui';
import WarningIcon from '@material-ui/icons/Warning';

const AlertBox = () => {
    const {
        state: {
            messages = [],
            messageTitle,
        },
        dispatch,
    } = useForm();

    if (!messages || messages.length === 0) {
        return null;
    }

    return (
        <div className="editorAlertBox">
            <Alert
                color="danger"
                onClose={() => dispatch({ type: FormActions.resetError })}
                elevation={2}
            >
                <div className="editorAlertBoxHeader">
                    <WarningIcon />
                    <strong>{messageTitle}</strong>
                </div>
                <List>
                    {messages.map((message, index) => (
                        <ListItemText
                            primaryTypographyProps={{ style: { fontSize: '1.4rem' } }}
                            key={message + index}
                        >
                            {message}
                        </ListItemText>))}
                </List>
            </Alert>
        </div>
    );
};

export default AlertBox;
