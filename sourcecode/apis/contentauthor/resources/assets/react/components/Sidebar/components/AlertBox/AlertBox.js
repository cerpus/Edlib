import React from 'react';
import { FormActions, useForm } from '../../../../contexts/FormContext';
import List from '@material-ui/core/List';
import ListItemText from '@material-ui/core/ListItemText';
import Alert from '@material-ui/lab/Alert';
import AlertTitle from '@material-ui/lab/AlertTitle';

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
                severity="error"
                onClose={() => dispatch({ type: FormActions.resetError })}
            >
                <AlertTitle>
                    <strong>{messageTitle}</strong>
                </AlertTitle>
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
