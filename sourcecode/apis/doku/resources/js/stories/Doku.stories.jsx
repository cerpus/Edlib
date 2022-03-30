import React, { useState } from 'react';

import { createEmptyEditorState, default as DokuEditor } from '../Doku';
import AuthWrapper from '../Doku/components/AuthWrapper';
import { EdlibComponentsProvider } from '../Doku/contexts/EdlibComponents';
import DokuContainer from '../Doku/Editors/Doku';
import { ThemeProvider, useTheme } from '../Doku/contexts/theme';

// More on default export: https://storybook.js.org/docs/react/writing-stories/introduction#default-export
export default {
  title: 'Doku',
  // More on argTypes: https://storybook.js.org/docs/react/api/argtypes
  argTypes: {},
};

export const Doku = () => {
    const edlibApiUrl = 'https://api.edlib.local';
    const theme = useTheme();

    return (
        <AuthWrapper edlibApiUrl={edlibApiUrl}>
            {({ getJwt, getLanguage }) => {
                return (
                    <EdlibComponentsProvider
                        edlibUrl={edlibApiUrl}
                        getJwt={getJwt}
                        language={getLanguage()}
                    >
                        <ThemeProvider
                            theme={{
                                ...theme,
                                doku: {
                                    defaultSpacing: 24,
                                },
                            }}
                        >
                            <DokuContainer />
                        </ThemeProvider>
                    </EdlibComponentsProvider>
                );
            }}
        </AuthWrapper>
    );
};

export const Editor = () => {
    const [editorState, setEditorState] = useState(createEmptyEditorState());
    const edlibApiUrl = 'https://api.edlib.local';

    return (
        <AuthWrapper edlibApiUrl={edlibApiUrl}>
            {({ getJwt, getLanguage }) => {
                return (
                    <EdlibComponentsProvider
                        edlibUrl={edlibApiUrl}
                        getJwt={getJwt}
                        language={getLanguage()}
                    >
                        <DokuEditor
                            editorState={editorState}
                            setEditorState={setEditorState}
                        />
                    </EdlibComponentsProvider>
                );
            }}
        </AuthWrapper>
    );
};
