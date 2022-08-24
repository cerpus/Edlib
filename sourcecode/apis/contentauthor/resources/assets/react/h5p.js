/* eslint-disable no-undef */
import React from 'react';
import ReactDOM from 'react-dom';
import H5pEditor from './components/H5pEditor';
import CerpusUI from './theme/cerpusUI';
import { FormProvider } from './contexts/FormContext';
import { EditorSetupProvider } from './contexts/EditorSetupContext';

ReactDOM.render(
    <CerpusUI>
        <FormProvider value={contentState}>
            <EditorSetupProvider
                {...editorSetup}
            >
                <H5pEditor />
            </EditorSetupProvider>
        </FormProvider>
    </CerpusUI>,
    document.getElementById('mainContent'));
