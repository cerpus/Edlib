/* eslint-disable no-undef */
import React from 'react';
import ReactDOM from 'react-dom';
import { QuestionContentContainer } from './components/QuestionContentType';
import CerpusUI from './theme/cerpusUI';
import { FormProvider } from './contexts/FormContext';
import { EditorSetupProvider } from './contexts/EditorSetupContext';

ReactDOM.render(
    <CerpusUI>
        <FormProvider value={contentState}>
            <EditorSetupProvider
                {...editorSetup}
            >
                <QuestionContentContainer />
            </EditorSetupProvider>
        </FormProvider>
    </CerpusUI>,
    document.getElementById('mainContent')
);
