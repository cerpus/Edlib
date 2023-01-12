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
        button: { textTransform: 'none' },
    },
});
