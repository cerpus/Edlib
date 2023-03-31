import * as i18nDataEnGb from '../language/en-gb';
import * as i18nDataNbNo from '../language/nb-no';
import * as i18nDataNnNo from '../language/nn-no';

const addLanguage = (languageCode) => {
    let i18nData;
    try {
        switch (languageCode) {
            case 'no':
                //No break;
            case 'nb':
                //No break;
            case 'nb-no':
                i18nData = i18nDataNbNo;
                break;
            case 'nn-no':
                i18nData = i18nDataNnNo;
                break
            default:
                i18nData = i18nDataEnGb;
                break;
        }
    } catch (ex) {
        // Ignore and use the default language
    }
    if (typeof i18nData === 'undefined' || i18nData === null) {
        i18nData = i18nDataEnGb;
    }
    return i18nData.default;
};

const langCode = window.navigator.userLanguage || window.navigator.language;
const defaultLanguage = addLanguage(langCode.toLowerCase());

export {
    defaultLanguage as default,
    addLanguage,
};
