import React from 'react';
import { useDokuContext } from '../../dokuContext';
import MathJax from '../../components/MathJax';

const InlineTex = ({ entityKey }) => {
    const { editorState, openMathModal, isEditing } = useDokuContext();
    const { mathML, tex } = editorState
        .getCurrentContent()
        .getEntity(entityKey)
        .getData();

    return (
        <span onClick={() => isEditing && openMathModal(entityKey)}>
            <MathJax.Node
                inline
                type={mathML ? 'mml' : 'tex'}
                formula={mathML || tex}
            />
        </span>
    );
};

export default InlineTex;
