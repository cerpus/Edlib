import React, { useEffect, useState } from 'react';
import { injectIntl } from 'react-intl';
import PropTypes from 'prop-types';
import Box from '@material-ui/core/Box';
import Button from '@material-ui/core/Button';
import { FormActions, useForm } from '../../../../contexts/FormContext';
import Axios from '../../../../utils/axiosSetup';

const SaveBox = ({ onSave, intl, onSaveCallback }) => {
    const { dispatch } = useForm();

    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        if (onSaveCallback) {
            onSaveCallback(saveForm);
        }
    }, []);

    const resetErrors = () => dispatch({ type: FormActions.resetError });
    const setErrors = (errors) =>
        dispatch({
            type: FormActions.setError,
            payload: {
                messages: errors,
                messageTitle: intl.formatMessage({ id: 'SAVEBOX.ERROR' }),
            },
        });

    const onClick =
        (isDraft = false) =>
        () => {
            const result = onSave(isDraft);
            if (typeof result === 'object') {
                saveForm(result);
            }
            return result;
        };

    const saveForm = (params) => {
        setProcessing(true);
        resetErrors();
        if (params === false) {
            return;
        }

        const {
            isValid,
            errorHandler: customErrorHandler,
            statusHandler,
            values,
            errorMessages,
        } = params;

        if (!isValid) {
            setErrors(errorMessages);
            return;
        }

        const formData = new FormData();
        Object.entries(values).forEach(([key, value]) => {
            let appendValue = value || '';
            if (typeof value === 'boolean') {
                appendValue = value | 0;
            }
            formData.append(key, appendValue);
        });

        Axios.post(values.route, formData)
            .then(({ data }) => {
                if (data.statuspath) {
                    getStatus(data.statuspath, data.url, statusHandler);
                } else if (data.url) {
                    storingComplete(data.url);
                }
            })
            .catch((response) => {
                setProcessing(false);
                let errorMessages;
                if (typeof customErrorHandler !== 'undefined') {
                    errorMessages = customErrorHandler(response);
                } else {
                    errorMessages = errorHandler(response);
                }
                setErrors(Array.isArray(errorMessages) ? [...new Set(errorMessages.flat())] : errorMessages);
            });
    };

    const storingComplete = (redirectUrl) =>
        window.location.replace(redirectUrl);

    const errorHandler = ({ response }) => {
        let responseData;
        try {
            responseData = Object.values(response.data.errors);
        } catch (err) {
            if (response.data.message) {
                responseData = [response.data.message];
            } else {
                responseData = [response.request.responseText];
            }
        }
        return responseData;
    };

    const getStatus = async (statusUrl, redirectUrl, statusCallback) => {
        try {
            const { data: status } = await Axios.get(statusUrl);
            if (status.error > 0) {
                console.log(status);
            } else if (status.left > 0) {
                statusCallback(status);
                setTimeout(() => {
                    getStatus(statusUrl, redirectUrl, statusCallback);
                }, 1000);
            } else if (status.left === 0) {
                storingComplete(redirectUrl);
            }
        } catch (error) {
            console.log(error);
        }
    };

    return (
        <>
            <Box pb={1}>
                <Button
                    id="ca-form-submit-btn"
                    onClick={onClick(false)}
                    variant="contained"
                    color="primary"
                    disabled={processing}
                    style={{textTransform: 'none', fontSize: '1.6rem', fontWeight: '400'}}
                >
                    {!processing &&
                        intl.formatMessage({ id: 'SAVEBOX.SAVEANDCLOSE' })}
                    {processing && (
                        <span>
                            <i className="fa fa-spinner fa-spin " />
                            {intl.formatMessage({ id: 'SAVEBOX.PROCESSING' })}
                        </span>
                    )}
                </Button>
            </Box>
            {/*<Box>*/}
            {/*    <Button*/}
            {/*        id="ca-form-submit-btn"*/}
            {/*        onClick={onClick(true)}*/}
            {/*        variant="contained"*/}
            {/*        color="gray"*/}
            {/*        disabled={processing}*/}
            {/*    >*/}
            {/*        {!processing &&*/}
            {/*            intl.formatMessage({ id: 'SAVEBOX.SAVEDRAFTANDCLOSE' })}*/}
            {/*        {processing && (*/}
            {/*            <span>*/}
            {/*                <i className="fa fa-spinner fa-spin " />*/}
            {/*                {intl.formatMessage({ id: 'SAVEBOX.PROCESSING' })}*/}
            {/*            </span>*/}
            {/*        )}*/}
            {/*    </Button>*/}
            {/*</Box>*/}
        </>
    );
};

SaveBox.propTypes = {
    onSave: PropTypes.func,
    onSaveCallback: PropTypes.func,
};

export default injectIntl(SaveBox);
