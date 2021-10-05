import React from 'react';
import i18nDefault, { addLanguage } from '../components/languageSetup';
import { IntlProvider } from 'react-intl';
import { ThemeProvider } from '@cerpus/ui';

if (!window.Intl) {
    require('intl');
    require('intl/locale-data/jsonp/en-US.js');
    require('intl/locale-data/jsonp/en-GB.js');
    require('intl/locale-data/jsonp/nb-NO.js');
}

const CerpusUI = ({children}) => {
    const editorContainer = document.getElementById('theBody');
    const bodyLanguageCode = editorContainer !== null ? editorContainer.getAttribute('data-locale') : null;
    const i18nData = bodyLanguageCode !== null ? addLanguage(bodyLanguageCode) : i18nDefault;

    return (
        <IntlProvider {...i18nData}>
            <ThemeProvider>
                {children}
            </ThemeProvider>
        </IntlProvider>
    );
};

export {
    CerpusUI as default,
};
