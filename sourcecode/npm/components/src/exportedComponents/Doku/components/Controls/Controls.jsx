import React from 'react';
import styled from 'styled-components';
import blockTypes from '../../draftJSHelpers/blockTypes';
import StyleButton from './components/StyleButton';
import inlineStyles from '../../draftJSHelpers/inlineStyles';
import { Functions } from '@material-ui/icons';
import IconButtonContent from './components/IconButtonContent';

const StyledControls = styled.div`
    border-bottom: 1px solid #ddd;
    margin-bottom: 10px;

    & > div {
        font-family: 'Helvetica', sans-serif;
        font-size: 14px;
        margin-bottom: 5px;
        user-select: none;
    }
`;

const Controls = ({
    editorState,
    onToggleBlockType,
    onToggleInlineStyle,
    onAddInlineText,
}) => {
    const currentStyle = editorState.getCurrentInlineStyle();
    const selection = editorState.getSelection();
    const blockType = editorState
        .getCurrentContent()
        .getBlockForKey(selection.getStartKey())
        .getType();

    return (
        <StyledControls>
            <div>
                {blockTypes.map((type) => (
                    <StyleButton
                        key={type.style}
                        active={type.style === blockType}
                        onToggle={() => onToggleBlockType(type.style)}
                    >
                        {type.label}
                    </StyleButton>
                ))}
            </div>
            <div>
                {inlineStyles.map((type) => (
                    <StyleButton
                        key={type.style}
                        active={currentStyle.has(type.style)}
                        onToggle={() => onToggleInlineStyle(type.style)}
                    >
                        {type.label}
                    </StyleButton>
                ))}
                <StyleButton onToggle={() => onAddInlineText()}>
                    <IconButtonContent icon={Functions} />
                </StyleButton>
            </div>
        </StyledControls>
    );
};

export default Controls;
