import React, { useState } from 'react';

import { createEmptyEditorState, default as Doku } from '../Doku';
import AuthWrapper from '../Doku/components/AuthWrapper';
import { EdlibComponentsProvider } from '../Doku/contexts/EdlibComponents';

// More on default export: https://storybook.js.org/docs/react/writing-stories/introduction#default-export
export default {
  title: 'The Doku',
  component: Doku,
  // More on argTypes: https://storybook.js.org/docs/react/api/argtypes
  argTypes: {},
};

export const Primary = () => {
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
                        <Doku
                            editorState={editorState}
                            setEditorState={setEditorState}
                        />
                    </EdlibComponentsProvider>
                );
            }}
        </AuthWrapper>
    );
};
