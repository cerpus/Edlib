import { useState } from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';
import Axios from 'axios';
import { iso6392 } from 'iso-639-2';
import { Button } from 'react-bootstrap';
import Dialog from '@material-ui/core/Dialog';
import CircularProgress from '@material-ui/core/CircularProgress';
import Select from '@material-ui/core/Select';
import MenuItem from '@material-ui/core/MenuItem';
import FormControl from '@material-ui/core/FormControl';
import InputLabel from '@material-ui/core/InputLabel';

const getTranslations = async (from, to, fields) => {
    const response = await Axios.post('/api/translate', {
        from_lang: from,
        to_lang: to,
        fields,
    });

    if (!response.data.document) {
        throw new Error('Missing "document" field');
    }

    return response.data.document;
};

const LanguagePicker = ({
    language,
    onChange,
    onGetFields,
    onSetFields,
    supportedLanguages,
}) => {
    const [originalLanguage, setOriginalLanguage] = useState(language);
    const [translateInProgress, setTranslateInProgress] = useState(false);

    const offerTranslation = supportedLanguages === null ||
        Object.hasOwn(supportedLanguages, originalLanguage);

    const canTranslate = originalLanguage !== language &&
        supportedLanguages === null || (
            Object.hasOwn(supportedLanguages, originalLanguage) &&
            supportedLanguages[originalLanguage].includes(language)
        );

    const handleLanguageChange = (event) => {
        onChange(event.target.value);
    };

    const handleTranslate = async () => {
        setTranslateInProgress(true);

        try {
            onSetFields(await getTranslations(originalLanguage, language, await onGetFields()));
            setOriginalLanguage(language);
        } finally {
            setTranslateInProgress(false);
        }
    };

    return (
        <div className="languagepicker-container">
            <FormControl fullWidth>
                <InputLabel id="languagepicker-label">
                    <FormattedMessage id="H5P_EDITOR.LANGUAGE_PICKER.LANGUAGE" />
                </InputLabel>

                <Select
                    labelId="languagepicker-label"
                    name="language_iso_639_3"
                    onChange={handleLanguageChange}
                    value={language}
                >
                    <MenuItem value="">
                        <FormattedMessage id="H5P_EDITOR.LANGUAGE_PICKER.NOT_CHOSEN" />
                    </MenuItem>
                    {iso6392.filter(l => l.iso6391).map(l => (
                        <MenuItem key={l.iso6392B} value={l.iso6392B}>
                            {l.name}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>

            {offerTranslation && (
                <div style={{ marginTop: 10 }}>
                    <Button
                        disabled={!canTranslate}
                        onClick={handleTranslate}
                        bsStyle="primary"
                    >
                        <FormattedMessage id="H5P_EDITOR.LANGUAGE_PICKER.TRANSLATE" />
                    </Button>

                    <Dialog open={translateInProgress}>
                        <div className="languagepicker-dialog-div">
                            <CircularProgress />
                            <div>
                                <FormattedMessage id="H5P_EDITOR.FETCHING_TRANSLATIONS.PLEASE_WAIT" />
                            </div>
                        </div>
                    </Dialog>
                </div>
            )}
        </div>
    );
}

LanguagePicker.propTypes = {
    language: PropTypes.string.isRequired,
    onChange: PropTypes.func.isRequired,
    onGetFields: PropTypes.func.isRequired,
    onSetFields: PropTypes.func.isRequired,
    supportedLanguages: PropTypes.object,
};

export default LanguagePicker;
