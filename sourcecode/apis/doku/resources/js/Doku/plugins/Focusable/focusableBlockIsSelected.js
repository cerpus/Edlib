export default (editorState, blockKeyStore) => {
    const selection = editorState.getSelection();
    if (selection.getAnchorKey() !== selection.getFocusKey()) {
        return false;
    }
    const content = editorState.getCurrentContent();
    const block = content.getBlockForKey(selection.getAnchorKey());
    return blockKeyStore.includes(block.getKey());
};
