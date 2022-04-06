import React from 'react';
import {
    ThemeContext,
    ThemeProvider as StyledComponentsThemeProvider,
} from 'styled-components';
import {
    createTheme,
    ThemeProvider as MaterialUiThemeProvider,
} from '@mui/material/styles';
import defaultMUITheme from '../themes/defaultMuiTheme';
import defaultStyledTheme from '../themes/defaultStyledTheme';
import merge from 'lodash.merge';

export const ThemeProvider = ({
                                  materialUITheme = {},
                                  theme = defaultStyledTheme,
                                  children,
                              }) => {
    const actualMaterialUITheme = React.useMemo(
        () => createTheme(merge(defaultMUITheme, materialUITheme)),
        [defaultMUITheme, materialUITheme]
    );

    return (
        <StyledComponentsThemeProvider
            theme={{
                ...theme,
                rem: (factor) => `${theme.fontSize * factor}px`,
            }}
        >
            <MaterialUiThemeProvider theme={actualMaterialUITheme}>
                {children}
            </MaterialUiThemeProvider>
        </StyledComponentsThemeProvider>
    );
};

export const useTheme = () => {
    const theme = React.useContext(ThemeContext);

    return theme ? theme : defaultStyledTheme;
};
