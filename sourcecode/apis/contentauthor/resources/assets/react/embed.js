/* eslint-disable no-undef */
import React from 'react';
import ReactDOM from 'react-dom';
import { EmbedContentType } from './components/EmbedContentType';
import CerpusUI from './theme/cerpusUI';
import { FormProvider } from './contexts/FormContext';

ReactDOM.render(
    <CerpusUI>
        <FormProvider value={contentState}>
            <EmbedContentType />
        </FormProvider>
    </CerpusUI>,
    document.getElementById('mainContent')
);

