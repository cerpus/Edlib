import React from 'react';
import i18nDefault, { addLanguage } from '../components/languageSetup';
import { IntlProvider } from 'react-intl';
import { createTheme, ThemeProvider } from '@material-ui/core/styles';
import CssBaseline from '@material-ui/core/CssBaseline';
import grey from '@material-ui/core/colors/grey';

const CerpusUI = ({ children }) => {
    const editorContainer = document.getElementById('theBody');
    const bodyLanguageCode =
        editorContainer !== null
            ? editorContainer.getAttribute('data-locale')
            : null;
    const i18nData =
        bodyLanguageCode !== null ? addLanguage(bodyLanguageCode) : i18nDefault;

    return (
        <IntlProvider {...i18nData} textComponent="span">
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
                        tertiary: {
                            main: '#2195f3',
                            dark: '#0067bf',
                        },
                    },
                    typography: {
                        htmlFontSize: 10,
                        fontFamily: "'Lato', sans-serif",
                        body1: {
                            fontSize: '1.4rem',
                        },
                    },
                    overrides: {
                        MuiAccordionDetails: {
                            root: {
                                display: 'block',
                            },
                        },
                        MuiTab:{
                            root: {
                                textTransform: 'none',
                            }
                        },
                        MuiAccordion: {
                            root: {
                                borderTop: '1px solid rgba(0, 0, 0, 0.12)',
                                '&:before': {
                                    opacity: '0',
                                },
                                '&.Mui-expanded': {
                                    margin: '0',
                                },
                            },
                        },
                        MuiAccordionSummary:{
                            root: {
                                '&.Mui-expanded': {
                                    minHeight: '0',
                                },
                            },
                            content: {
                                '&.Mui-expanded': {
                                    margin: '12px 0',
                                    fontWeight: 'bold',
                                },
                            },
                        },
                    },
                    props: {
                        MuiAccordion: {
                            variant: 'elevation',
                            elevation: '0',
                        },
                    },
                })}
            >
                <CssBaseline />
                {children}
            </ThemeProvider>
        </IntlProvider>
    );
};

export { CerpusUI as default };
