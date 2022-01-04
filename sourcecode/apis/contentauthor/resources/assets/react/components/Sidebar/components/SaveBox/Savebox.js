import React, { useEffect, useState } from 'react';
import { injectIntl, intlShape } from 'react-intl';
import PropTypes from 'prop-types';
import { Button } from '@cerpus/ui';
import { FormActions, useForm } from '../../../../contexts/FormContext';
import Axios from '../../../../utils/axiosSetup';

const SaveBox = ({ onSave, intl, onSaveCallback, pulseUrl }) => {
    const {
        dispatch,
    } = useForm();

    const [processing, setProcessing] = useState(false);
    let pulseLockInterval;

    const pollStatus = () => Axios
        .post(pulseUrl);

    useEffect(() => {
        if (onSaveCallback) {
            onSaveCallback(saveForm);
        }

        if (pulseUrl) {
            pulseLockInterval = setInterval(pollStatus, 60000);
        }
        return () => {
            if (pulseLockInterval) {
                clearInterval(pulseLockInterval);
            }
        };
    }, []);

    const resetErrors = () => dispatch({ type: FormActions.resetError });
    const setErrors = errors => dispatch({
        type: FormActions.setError,
        payload: {
            messages: errors,
            messageTitle: intl.formatMessage({ id: 'SAVEBOX.ERROR' }),
        },
    });

    const onClick = () => {
        const result = onSave();
        if (typeof result === 'object') {
            saveForm(result);
        }
        return result;
    };

    const saveForm = params => {
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
            .catch(response => {
                setProcessing(false);
                let errorMessages;
                if (typeof customErrorHandler !== 'undefined') {
                    errorMessages = customErrorHandler(response);
                } else {
                    errorMessages = errorHandler(response);
                }
                setErrors(errorMessages);
            });
    };

    const storingComplete = redirectUrl => window.location.replace(redirectUrl);

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
        <Button
            id="ca-form-submit-btn"
            onClick={onClick}
            type={'tertiary'}
            disabled={processing}
            data-loading-text={'<span><i class=\'fa fa-spinner fa-spin \' /> ' + intl.formatMessage({ id: 'SAVEBOX.PROCESSING' }) + '</span>'}
        >
            {!processing && (
                intl.formatMessage({ id: 'SAVEBOX.SAVEANDCLOSE' })
            )}
            {processing && (
                <span><i className="fa fa-spinner fa-spin " />{intl.formatMessage({ id: 'SAVEBOX.PROCESSING' })}</span>
            )}
        </Button>
    );
};

SaveBox.propTypes = {
    onSave: PropTypes.func,
    onSaveCallback: PropTypes.func,
    intl: intlShape,
    pulseUrl: PropTypes.string,
};

export default injectIntl(SaveBox);
