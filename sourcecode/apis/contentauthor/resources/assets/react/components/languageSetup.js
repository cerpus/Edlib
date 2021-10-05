
if (!window.Intl) {
    require('intl');
    require('intl/locale-data/jsonp/en-US.js');
    require('intl/locale-data/jsonp/en-GB.js');
    require('intl/locale-data/jsonp/nb-NO.js');
    require('intl/locale-data/jsonp/sv-SE.js');
}

import { addLocaleData } from 'react-intl';
import localeEn from 'react-intl/locale-data/en';
import localeNb from 'react-intl/locale-data/nb';
import localeSv from 'react-intl/locale-data/sv';

import * as i18nDataEnGb from '../language/en-gb';
import * as i18nDataNbNo from '../language/nb-no';
import * as i18nDataSvSe from '../language/sv-se';

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
                addLocaleData(localeNb);
                break;
            case 'sv-se':
                i18nData = i18nDataSvSe;
                addLocaleData(localeSv);
                break;
            default:
                i18nData = i18nDataEnGb;
                addLocaleData(localeEn);
                break;
        }
    } catch (ex) {
        // Ignore and use the default language
    }
    if (typeof i18nData === 'undefined' || i18nData === null) {
        i18nData = i18nDataEnGb;
        addLocaleData(localeEn);
    }
    return i18nData.default;
};

const langCode = window.navigator.userLanguage || window.navigator.language;
const defaultLanguage = addLanguage(langCode.toLowerCase());

export {
    defaultLanguage as default,
    addLanguage,
};
