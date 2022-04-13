import removeBlock from './removeBlock';
import { EditorState } from 'draft-js';

export default function (editorState) {
    const selection = editorState.getSelection();
    const content = editorState.getCurrentContent();
    const key = selection.getAnchorKey();
    const offset = selection.getAnchorOffset();
    const block = content.getBlockForKey(key);

    // Problematic selection. Pressing delete here would remove the entity, but not the block.
    if (
        selection.isCollapsed() &&
        block.getType() === 'atomic' &&
        offset === 0
    ) {
        const newContent = removeBlock(
            content,
            block.getKey()
        );
        return EditorState.push(editorState, newContent, "change-block-type");
    }

    return false;
};
