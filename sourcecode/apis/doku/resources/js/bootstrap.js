import { useState, StrictMode } from 'react';
import { render } from 'react-dom';
import { BrowserRouter } from 'react-router-dom';
import { createEmptyEditorState, default as Doku } from './Doku';

const app = document.querySelector('#app');

const App = () => {
    const [editorState, setEditorState] = useState(createEmptyEditorState());

    return <Doku
        editorState={editorState}
        setEditorState={setEditorState}
    />;
};

render(
    <StrictMode>
        <BrowserRouter>
            <App />
        </BrowserRouter>
    </StrictMode>
, app);
