/* eslint-disable no-undef */
import React from 'react';
import ReactDOM from 'react-dom';
import CerpusUI from './theme/cerpusUI';
import Article from './components/Article';
import { FormProvider } from './contexts/FormContext';

ReactDOM.render(
    <CerpusUI>
        <FormProvider value={contentState}>
            <Article
                editorSetup={editorSetup}
                articleSetup={ArticleIntegration}
                uploadUrl={uploadUrl}
            />
        </FormProvider>
    </CerpusUI>,
    document.getElementById('mainContent')
);
