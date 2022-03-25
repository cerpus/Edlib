import React from 'react';
import cn from 'classnames';
import { SelectionState, EditorState } from 'draft-js';
import DraftOffsetKey from 'draft-js/lib/DraftOffsetKey';
import { useDokuContext } from '../../dokuContext';
import styled from 'styled-components';

const Wrapper = styled.div`
    border: 4px solid transparent;

    &:hover,
    &.focused {
        border: 4px solid ${(props) => props.theme.colors.tertiary};
    }
`;

const setSelectionToBlock = (editorState, newActiveBlock) => {
    const offsetKey = DraftOffsetKey.encode(newActiveBlock.getKey(), 0, 0);
    const node = document.querySelectorAll(
        `[data-offset-key="${offsetKey}"]`
    )[0];
    const selection = window.getSelection();
    const range = document.createRange();
    range.setStart(node, 0);
    range.setEnd(node, 0);
    selection.removeAllRanges();
    selection.addRange(range);

    return EditorState.forceSelection(
        editorState,
        new SelectionState({
            anchorKey: newActiveBlock.getKey(),
            anchorOffset: 0,
            focusKey: newActiveBlock.getKey(),
            focusOffset: 0,
            isBackward: false,
        })
    );
};

const FocusDecorator = ({ contentBlock, children }) => {
    const {
        editorState,
        setEditorState,
        isBlockSelected,
        focusableBlocksStore,
    } = useDokuContext();

    React.useEffect(() => {
        const key = contentBlock.getKey();
        focusableBlocksStore.add(key);

        return () => {
            focusableBlocksStore.remove(key);
        };
    });

    if (contentBlock.getType() !== 'atomic') {
        return <></>;
    }

    const isFocused = isBlockSelected(contentBlock.getKey());

    return (
        <Wrapper
            onClick={(e) => {
                e.preventDefault();
                if (!isFocused) {
                    setEditorState(
                        setSelectionToBlock(editorState, contentBlock)
                    );
                }
            }}
            className={cn({
                focused: isFocused,
            })}
        >
            {children({
                isFocused,
            })}
        </Wrapper>
    );
};

export default FocusDecorator;
