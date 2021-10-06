/* eslint-disable no-undef */
import React from 'react';
import ReactDOM from 'react-dom';
import H5pEditor from './components/H5pEditor';
import CerpusUI from './theme/cerpusUI';
import { FormProvider } from './contexts/FormContext';

ReactDOM.render(
    <CerpusUI>
        <FormProvider value={contentState}>
            <H5pEditor
                editorSetup={editorSetup}
            />
        </FormProvider>
    </CerpusUI>,
    document.getElementById('mainContent'));
