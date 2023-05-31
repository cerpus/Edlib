import createCache from '@emotion/cache';
import { createTheme } from '@mui/material/styles';
import { grey } from '@mui/material/colors';

export const createEmotionCache = () => createCache({ key: 'css' });

export const muiTheme = createTheme({
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
        htmlFontSize: 16,
        fontFamily: "'Lato', sans-serif",
        fontWeight: '400',
        button: { textTransform: 'none' },
    },
    components: {
        MuiMenuItem: {
            styleOverrides: {
                root: {
                    '@media(forced-colors: active)': {
                        '&:hover': {
                            color: 'Highlight',
                        },
                    },
                },
            },
        },
        MuiOutlinedInput: {
            styleOverrides: {
                notchedOutline: {
                    '@media(forced-colors: active)': {
                        borderColor: 'currentColor',
                    },
                },
            },
        },
        MuiPopover: {
            styleOverrides: {
                paper: {
                    '@media(forced-colors: active)': {
                        border: '1px solid Highlight',
                    },
                },
            },
        },
        MuiAutocomplete: {
            styleOverrides: {
                paper: {
                    '@media(forced-colors: active)': {
                        border: '1px solid Highlight',
                    },
                },
            },
        },
        MuiDialog: {
            styleOverrides: {
                paper: {
                    '@media(forced-colors: active)': {
                        border: '1px solid Highlight',
                    },
                },
            },
        },
        MuiButton: {
            styleOverrides: {
                root: {
                    '@media(forced-colors: active)': {
                        '&:hover': {
                            outline: '1px solid ButtonText',
                        },
                    },
                },
            },
        },
        MuiPaper: {
            styleOverrides: {
                root: {
                    '@media(forced-colors: active)': {
                        border: '1px solid currentColor',
                    },
                },
            },
        },
    },
});
