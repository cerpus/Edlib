import React from 'react';
import { convertFromRaw, EditorState, convertToRaw } from 'draft-js';
import { useDokuContext } from '../dokuContext';
import Editor from '../components/Editor';
import { decorators } from '../index';

const InBlockEditor = ({ editorState, onUpdate }, ref) => {
    const { setSubEditorHasFocus, isEditing } = useDokuContext();
    const [editorStateCurrent, _setEditorState] = React.useState(() =>
        EditorState.createWithContent(convertFromRaw(editorState), decorators)
    );

    const setEditorState = (editorState) => {
        _setEditorState(editorState);
        onUpdate({
            editorState: convertToRaw(editorState.getCurrentContent()),
        });
    };

    return (
        <Editor
            readOnly={!isEditing}
            onFocus={() => setSubEditorHasFocus(true)}
            onBlur={() => setSubEditorHasFocus(false)}
            ref={ref}
            editorState={editorStateCurrent}
            setEditorState={setEditorState}
        />
    );
};

export default React.forwardRef(InBlockEditor);
