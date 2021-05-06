import React from 'react';
import { ThemeProvider, themes } from '@cerpus/ui';

const ExportWrapper = ({ children }) => (
    <ThemeProvider materialUITheme={themes.edlib}>{children}</ThemeProvider>
);

export default ExportWrapper;
