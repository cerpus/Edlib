import React, { useMemo } from 'react';
import { injectIntl } from 'react-intl';
import PropTypes from 'prop-types';
import getTranslations from './language/translations';
import { useForm, FormActions } from '../../../../contexts/FormContext';
import FormControlLabel from '@material-ui/core/FormControlLabel';
import CheckBox from '@material-ui/core/Checkbox';
import FormGroup from '@material-ui/core/FormGroup';

const initialState = (displayButtons, displayDownload, displayCopyright) => ({
    download: displayDownload,
    copyright: displayCopyright,
    frame: displayButtons,
});

const DisplayOptions = ({ displayButtons, displayCopyright, displayDownload, intl }) => {
    const translations = useMemo(() => getTranslations(intl), [intl]);
    const { dispatch } = useForm();
    const state = useMemo(() => initialState(displayButtons, displayDownload, displayCopyright), [displayButtons, displayDownload, displayCopyright]);

    const triggerChange = newState => dispatch({
        type: FormActions.setDisplayOptions,
        payload: { ...state, ...newState },
    });

    const triggerConditionally = newState => {
        if (displayButtons) {
            triggerChange(newState);
        }
    };

    return (
        <FormGroup>
            <FormControlLabel
                control={
                    <CheckBox
                        checked={displayButtons}
                        onChange={() => triggerChange({ frame: !displayButtons, copyright: false, download: false })}
                        color="primary"
                    />
                }
                label={translations.displayButtons}
            />
            <FormControlLabel
                control={
                    <CheckBox
                        checked={displayCopyright}
                        onChange={() => triggerConditionally({ copyright: !displayCopyright })}
                        color="primary"
                        disabled={!displayButtons}
                    />
                }
                label={translations.displayCopyright}
            />
            <FormControlLabel
                control={
                    <CheckBox
                        checked={displayDownload}
                        onChange={() => triggerConditionally({ download: !displayDownload })}
                        color="primary"
                        disabled={!displayButtons}
                    />
                }
                label={translations.displayDownload}
            />
        </FormGroup>
    );
};

DisplayOptions.propTypes = {
    displayButtons: PropTypes.bool.isRequired,
    displayCopyright: PropTypes.bool,
    displayDownload: PropTypes.bool,
};

export default injectIntl(DisplayOptions);
