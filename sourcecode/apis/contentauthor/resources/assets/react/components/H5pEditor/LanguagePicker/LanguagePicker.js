import React from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage, injectIntl } from 'react-intl';
import iso6392 from 'iso-639-2';
import { Dialog, CircularProgress } from '@material-ui/core';
import { Checkbox } from '@cerpus/ui';

const LanguagePicker = ({ languageValue, intl, hideNewVariant, onChange, isUpdateInProgress, isNewLanguageVariant }) => {
    return (
        <div className="languagepicker-container">
            <div>
                <select
                    name="language_iso_639_3"
                    value={languageValue}
                    onChange={e => onChange(e.target.value, isNewLanguageVariant)}
                    style={{ width: '100%' }}
                >
                    <option value="">{intl.formatMessage({ id: 'H5P_EDITOR.LANGUAGE_PICKER.NOT_CHOSEN' })}</option>
                    {iso6392.filter(l => l.iso6391).map(l => (
                        <option
                            key={l.iso6392B}
                            value={l.iso6392B}
                        >{l.name}</option>
                    ))}
                </select>
            </div>
            {!hideNewVariant && (
                <div style={{ marginTop: 10 }}>
                    <Checkbox
                        color="tertiary"
                        checked={isNewLanguageVariant}
                        onToggle={() => onChange(languageValue, !isNewLanguageVariant)}
                    >
                        <FormattedMessage id="H5P_EDITOR.LANGUAGE_PICKER.MAKE_NEW_VARIANT" />
                    </Checkbox>
                    <Dialog open={isUpdateInProgress}>
                        <div className="languagepicker-dialog-div">
                            <CircularProgress />
                            <div>
                                {intl.formatMessage({ id: 'H5P_EDITOR.FETCHING_TRANSLATIONS.PLEASE_WAIT' })}
                            </div>
                        </div>
                    </Dialog>
                </div>
            )}
        </div>
    );
};

LanguagePicker.propTypes = {
    languageValue: PropTypes.string,
    hideNewVariant: PropTypes.bool,
    isNewLanguageVariant: PropTypes.bool,
    onChange: PropTypes.func,
    isUpdateInProgress: PropTypes.bool,
};

export default injectIntl(LanguagePicker);
