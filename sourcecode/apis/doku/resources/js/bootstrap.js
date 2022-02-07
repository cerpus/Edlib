import { useState, StrictMode } from 'react';
import { render } from 'react-dom';
import { createEmptyEditorState, Doku } from '@cerpus/edlib-components';

const app = document.querySelector('#app');

const App = () => {
    const [editorState, setEditorState] = useState(createEmptyEditorState());

    return <Doku
        editorState={editorState}
        setEditorState={setEditorState}
    />;
};

render(<StrictMode><App /></StrictMode>, app);
