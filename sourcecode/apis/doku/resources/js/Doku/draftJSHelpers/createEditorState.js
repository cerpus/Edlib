import { EditorState, convertFromRaw } from 'draft-js';
import decorators from '../decorators';

export const createEmptyEditorState = () => EditorState.createEmpty(decorators);
export const createFromRaw = (content) =>
    EditorState.createWithContent(convertFromRaw(content), decorators);
