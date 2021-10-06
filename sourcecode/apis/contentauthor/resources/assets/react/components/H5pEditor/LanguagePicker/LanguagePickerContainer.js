import React from 'react';
import LanguagePicker from './LanguagePicker';
import getTextFields from '../List/getTextFields';
import Axios from 'axios';
import { deepCopy, set } from 'utils/utils';
import { FormActions, useForm } from 'contexts/FormContext';
import PropTypes from 'prop-types';

const getTranslations = async fields => {
    return await Axios.post('/v1/translate', { fields });
};

const flattenPath = (aggregatedPath, currentPath) => {
    aggregatedPath.push(currentPath.index !== undefined ? currentPath.index : currentPath);
    return aggregatedPath;
};

const LanguagePickerContainer = ({
    library,
    libraryCache,
    hideNewVariant,
    isNewLanguageVariant,
    autoTranslateTo,
    value,
    setParams,
    getParameters,
    ...restOfProps
}) => {
    const [translateFields, setTranslateFields] = React.useState();
    const [updateInProgress, setUpdateProgress] = React.useState(false);
    const { dispatch } = useForm();

    React.useEffect(() => {
        if (translateFields) {
            getTranslations(translateFields)
                .then(({ data }) => addTranslations(data.document))
                .catch(() => setUpdateProgress(false));
        }
    }, [translateFields]);

    React.useEffect(() => autoTranslate(value, isNewLanguageVariant), []);

    const addTranslations = translations => {
        const paramsCopy = deepCopy(getParameters());
        // eslint-disable-next-line no-unused-vars
        for (const i in translations) {
            set(paramsCopy.params, i, translations[i]);
        }
        setParams(paramsCopy);
        setUpdateProgress(false);
    };

    const autoTranslate = (language, isNewVariant) => {
        if (isNewVariant && autoTranslateTo && language === autoTranslateTo) {
            setUpdateProgress(true);
            getTextFields(getParameters(), library, libraryCache())
                .then(fields => setTranslateFields(fields.map(field => ({
                    path: field.path.reduce(flattenPath, []).join('.'),
                    value: field.originalValue,
                }))));
        }
    };

    const triggerTranslate = (language, isNewVariant) => {
        dispatch({ type: FormActions.setLanguage, payload: { language, isNewVariant } });
        autoTranslate(language, isNewVariant);
    };

    return (
        <LanguagePicker
            {...restOfProps}
            languageValue={value}
            hideNewVariant={hideNewVariant}
            onChange={triggerTranslate}
            isNewLanguageVariant={isNewLanguageVariant}
            isUpdateInProgress={updateInProgress}
        />
    );
};

LanguagePickerContainer.propTypes = {
    getParameters: PropTypes.func,
    library: PropTypes.string,
    libraryCache: PropTypes.func,
    hideNewVariant: PropTypes.bool,
    isNewLanguageVariant: PropTypes.bool,
    autoTranslateTo: PropTypes.string,
    value: PropTypes.string,
    setParams: PropTypes.func,
};

export default LanguagePickerContainer;
