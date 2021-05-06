import { SelectionState, AtomicBlockUtils, EditorState } from 'draft-js';

export default (
    editorState,
    type,
    data,
    moveSelectionToEnd = false,
    mutability = 'IMMUTABLE'
) => {
    if (moveSelectionToEnd) {
        const lastBlock = editorState.getCurrentContent().getLastBlock();

        if (lastBlock) {
            const key = lastBlock.getKey();
            const length = lastBlock.getLength();
            const selection = new SelectionState({
                anchorKey: key,
                anchorOffset: length,
                focusKey: key,
                focusOffset: length,
            });

            editorState = EditorState.forceSelection(editorState, selection);
        }
    }

    const contentState = editorState.getCurrentContent();
    const contentStateWithEntity = contentState.createEntity(
        type,
        mutability,
        data
    );

    return AtomicBlockUtils.insertAtomicBlock(
        EditorState.set(editorState, {
            currentContent: contentStateWithEntity,
        }),
        contentStateWithEntity.getLastCreatedEntityKey(),
        ' '
    );
};
