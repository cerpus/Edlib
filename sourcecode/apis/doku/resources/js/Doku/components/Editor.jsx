import React from 'react';
import { Editor as DraftEditor, getDefaultKeyBinding } from 'draft-js';
import { blockRenderer, blockRenderMap } from '../blockTypes';
import { RichUtils } from 'draft-js';
import { useDokuContext } from '../dokuContext';
import blockStyleFn from '../draftJSHelpers/blockStyleFn';
import { keyBindingFn } from '../plugins/Focusable';

const Editor = (
    { setEditorState, editorState, focusableBlocksStore, ...props },
    ref
) => {
    const { isEditing } = useDokuContext();

    return (
        <DraftEditor
            ref={ref}
            readOnly={!isEditing}
            editorState={editorState}
            onChange={setEditorState}
            blockRendererFn={blockRenderer}
            blockRenderMap={blockRenderMap}
            blockStyleFn={blockStyleFn}
            handleKeyCommand={(command, editorState) => {
                const newState = RichUtils.handleKeyCommand(
                    editorState,
                    command
                );

                if (newState) {
                    setEditorState(newState);
                    return 'handled';
                }

                return 'not-handled';
            }}
            keyBindingFn={(e) => {
                const handled = keyBindingFn(e, {
                    editorState,
                    setEditorState,
                    focusableBlocksStore,
                });

                if (handled) {
                    return;
                }

                return getDefaultKeyBinding(e);
            }}
            {...props}
        />
    );
};

export default React.forwardRef(Editor);
