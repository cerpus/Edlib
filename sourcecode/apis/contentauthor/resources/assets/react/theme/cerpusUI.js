import React from 'react';
import i18nDefault, { addLanguage } from '../components/languageSetup';
import { IntlProvider } from 'react-intl';
import { ThemeProvider as CerpusThemeProvider } from '@cerpus/ui';
import { createTheme, CssBaseline, ThemeProvider } from '@material-ui/core';
import { grey } from '@material-ui/core/colors';

if (!window.Intl) {
    require('intl');
    require('intl/locale-data/jsonp/en-US.js');
    require('intl/locale-data/jsonp/en-GB.js');
    require('intl/locale-data/jsonp/nb-NO.js');
}

const CerpusUI = ({ children }) => {
    const editorContainer = document.getElementById('theBody');
    const bodyLanguageCode =
        editorContainer !== null
            ? editorContainer.getAttribute('data-locale')
            : null;
    const i18nData =
        bodyLanguageCode !== null ? addLanguage(bodyLanguageCode) : i18nDefault;

    return (
        <IntlProvider {...i18nData}>
            <CerpusThemeProvider>
                <ThemeProvider
                    theme={createTheme({
                        palette: {
                            grey: {
                                main: grey[300],
                                dark: grey[400],
                            },
                            primary: {
                                main: '#21456A',
                                dark: '#21456A',
                            },
                            secondary: {
                                main: '#82E066',
                                dark: '#1D7105',
                            },
                        },
                        typography: {
                            htmlFontSize: 10,
                            fontFamily: "'Lato', sans-serif",
                        },
                    })}
                >
                    <CssBaseline />
                    {children}
                </ThemeProvider>
            </CerpusThemeProvider>
        </IntlProvider>
    );
};

export { CerpusUI as default };
