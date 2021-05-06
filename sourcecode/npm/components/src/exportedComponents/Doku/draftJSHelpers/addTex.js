import { Modifier, EditorState } from 'draft-js';
import atomicTypes from '../../../config/atomicTypes';

const isAtEndOfBlock = (contentState, selection) => {
    const currentBlockKey = selection.getAnchorKey();
    const currentBlock = contentState.getBlockForKey(currentBlockKey);
    return currentBlock.getText().length === selection.getStartOffset();
};

const insertInlineTeX = (editorState) => {
    let contentState = editorState.getCurrentContent();
    let selection = editorState.getSelection();

    let teX = '';

    if (!selection.isCollapsed()) {
        const blockKey = selection.getStartKey();
        if (blockKey === selection.getEndKey()) {
            teX = contentState
                .getBlockForKey(blockKey)
                .getText()
                .slice(selection.getStartOffset(), selection.getEndOffset());
        }
        contentState = Modifier.removeRange(
            contentState,
            selection,
            'backward'
        );
        selection = contentState.getSelectionAfter();
    }

    contentState = contentState.createEntity(atomicTypes.MATH, 'IMMUTABLE', {
        tex: teX,
    });

    const entityKey = contentState.getLastCreatedEntityKey();
    const atBeginOfBlock = selection.getStartOffset() === 0;
    const atEndOfBlock = isAtEndOfBlock(contentState, selection);

    if (atBeginOfBlock) {
        contentState = Modifier.insertText(contentState, selection, ' ');
        selection = contentState.getSelectionAfter();
    }

    contentState = Modifier.insertText(
        contentState,
        selection,
        '\t\t',
        undefined,
        entityKey
    );
    selection = contentState.getSelectionAfter();

    if (atEndOfBlock) {
        contentState = Modifier.insertText(contentState, selection, ' ');
    }

    return EditorState.push(editorState, contentState, 'apply-entity');
};

export default (editorState, block = false) => {
    return insertInlineTeX(editorState);
};
