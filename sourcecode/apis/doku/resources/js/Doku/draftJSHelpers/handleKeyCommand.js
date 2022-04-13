import deleteAtomicBlock from './deleteAtomicBlock';
import { RichUtils } from 'draft-js';

export default function (editorState, command) {
    let newState = RichUtils.handleKeyCommand(
        editorState,
        command
    );

    if (command === 'delete') {
        const afterState = deleteAtomicBlock(editorState);
        if (afterState !== false) {
            newState = afterState;
        }
    }

    return newState;
};
