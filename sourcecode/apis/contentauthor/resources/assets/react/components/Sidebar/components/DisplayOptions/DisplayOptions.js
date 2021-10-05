import React, { useMemo } from 'react';
import { injectIntl, intlShape } from 'react-intl';
import PropTypes from 'prop-types';
import { Checkbox } from '@cerpus/ui';
import getTranslations from './language/translations';
import { useForm, FormActions } from 'contexts/FormContext';

const initialState = (displayButtons, displayDownload, displayCopyright) => (
    {
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
        <>
            <Checkbox
                color="tertiary"
                checked={displayButtons}
                onToggle={() => triggerChange({ frame: !displayButtons, copyright: false, download: false })}
            >{translations.displayButtons}</Checkbox>
            <Checkbox
                color="tertiary"
                checked={displayCopyright}
                onToggle={() => triggerConditionally({ copyright: !displayCopyright })}
                disabled={!displayButtons}
            >{translations.displayCopyright}</Checkbox>
            <Checkbox
                color="tertiary"
                checked={displayDownload}
                onToggle={() => triggerConditionally({ download: !displayDownload })}
                disabled={!displayButtons}
            >{translations.displayDownload}</Checkbox>
        </>
    );
};

DisplayOptions.propTypes = {
    displayButtons: PropTypes.bool.isRequired,
    displayCopyright: PropTypes.bool,
    displayDownload: PropTypes.bool,
    intl: intlShape,
};

export default injectIntl(DisplayOptions);
