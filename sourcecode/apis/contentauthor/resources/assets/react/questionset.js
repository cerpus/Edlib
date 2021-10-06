/* eslint-disable no-undef */
import React from 'react';
import ReactDOM from 'react-dom';
import { QuestionContentContainer } from './components/QuestionContentType';
import CerpusUI from './theme/cerpusUI';
import { FormProvider } from './contexts/FormContext';

ReactDOM.render(
    <CerpusUI>
        <FormProvider value={contentState}>
            <QuestionContentContainer />
        </FormProvider>
    </CerpusUI>,
    document.getElementById('mainContent')
);
