import setSelection from './setSelection';
import focusableBlockIsSelected from './focusableBlockIsSelected';

export default (evt, { editorState, setEditorState, focusableBlocksStore }) => {
    if (focusableBlockIsSelected(editorState, focusableBlocksStore)) {
        // arrow left
        if (evt.keyCode === 37) {
            setSelection(editorState, setEditorState, 'up', evt);
        }
        // arrow right
        if (evt.keyCode === 39) {
            setSelection(editorState, setEditorState, 'down', evt);
        }
        // arrow up
        if (evt.keyCode === 38) {
            setSelection(editorState, setEditorState, 'up', evt);
        }
        // arrow down
        if (evt.keyCode === 40) {
            setSelection(editorState, setEditorState, 'down', evt);
            return null;
        }
    }

    // Don't manually overwrite in case the shift key is used to avoid breaking
    // native behaviour that works anyway.
    if (evt.shiftKey) {
        return null;
    }

    if (evt.key === 'Backspace') {
        // Covering the case to select the before block
        const selection = editorState.getSelection();
        const selectionKey = selection.getAnchorKey();
        const beforeBlock = editorState
            .getCurrentContent()
            .getBlockBefore(selectionKey);
        // only if the selection caret is at the left most position
        if (
            beforeBlock &&
            selection.getAnchorOffset() === 0 &&
            focusableBlocksStore.includes(beforeBlock.getKey())
        ) {
            setSelection(editorState, setEditorState, 'up', evt);
            return true;
        }
    }

    // arrow left
    if (evt.keyCode === 37) {
        // Covering the case to select the before block
        const selection = editorState.getSelection();
        const selectionKey = selection.getAnchorKey();
        const beforeBlock = editorState
            .getCurrentContent()
            .getBlockBefore(selectionKey);
        // only if the selection caret is at the left most position
        if (
            beforeBlock &&
            selection.getAnchorOffset() === 0 &&
            focusableBlocksStore.includes(beforeBlock.getKey())
        ) {
            setSelection(editorState, setEditorState, 'up', evt);
        }
    }

    // arrow right
    if (evt.keyCode === 39) {
        // Covering the case to select the after block
        const selection = editorState.getSelection();
        const selectionKey = selection.getFocusKey();
        const currentBlock = editorState
            .getCurrentContent()
            .getBlockForKey(selectionKey);
        const afterBlock = editorState
            .getCurrentContent()
            .getBlockAfter(selectionKey);
        const notAtomicAndLastPost =
            currentBlock.getType() !== 'atomic' &&
            currentBlock.getLength() === selection.getFocusOffset();
        if (
            afterBlock &&
            notAtomicAndLastPost &&
            focusableBlocksStore.includes(afterBlock.getKey())
        ) {
            setSelection(editorState, setEditorState, 'down', evt);
        }
    }

    // arrow up
    if (evt.keyCode === 38) {
        // Covering the case to select the before block with arrow up
        const selectionKey = editorState.getSelection().getAnchorKey();
        const beforeBlock = editorState
            .getCurrentContent()
            .getBlockBefore(selectionKey);
        if (
            beforeBlock &&
            focusableBlocksStore.includes(beforeBlock.getKey())
        ) {
            setSelection(editorState, setEditorState, 'up', evt);
        }
    }

    // arrow down
    if (evt.keyCode === 40) {
        // Covering the case to select the after block with arrow down
        const selectionKey = editorState.getSelection().getAnchorKey();
        const afterBlock = editorState
            .getCurrentContent()
            .getBlockAfter(selectionKey);
        if (afterBlock && focusableBlocksStore.includes(afterBlock.getKey())) {
            setSelection(editorState, setEditorState, 'down', evt);
        }
    }

    return null;
};
