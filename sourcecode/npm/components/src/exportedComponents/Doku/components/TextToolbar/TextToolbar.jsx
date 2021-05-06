import React from 'react';
import { getVisibleSelectionRect } from 'draft-js';
import { StyleButton, Splitter, Toolbar } from '../Toolbar';
import MaterialIcon from '../../../../components/Icons/Material';

const TextToolbar = ({
    editorState,
    editorRef,
    onToggleBlockType,
    onToggleInlineStyle,
    onAddInlineText,
}) => {
    const [offset, setOffset] = React.useState(null);
    const toolbarRef = React.useRef();
    const currentStyle = editorState.getCurrentInlineStyle();
    const selection = editorState.getSelection();
    const blockType = editorState
        .getCurrentContent()
        .getBlockForKey(selection.getStartKey())
        .getType();

    const selectionKey = React.useMemo(() => {
        return `${selection.getAnchorKey()}_${selection.getAnchorOffset()}_${selection.getFocusKey()}_${selection.getFocusOffset()}`;
    }, [selection]);

    React.useLayoutEffect(() => {
        const visibleSelectionRect = getVisibleSelectionRect(window);
        const editorRoot = editorRef.current.editor;

        if (!visibleSelectionRect || !editorRoot) {
            return setOffset(null);
        }

        const editorRootRect = editorRoot.getBoundingClientRect();

        if (!editorRootRect) {
            return setOffset(null);
        }

        if (visibleSelectionRect.width < 1) {
            return setOffset(null);
        }

        const extraTopOffset = -15;

        setOffset({
            top:
                visibleSelectionRect.top -
                editorRootRect.top -
                toolbarRef.current.offsetHeight +
                extraTopOffset,
            left: Math.max(
                visibleSelectionRect.left -
                    editorRootRect.left +
                    visibleSelectionRect.width / 2 -
                    toolbarRef.current.offsetWidth / 2,
                0
            ),
        });
    }, [selectionKey]);

    return (
        <Toolbar
            ref={toolbarRef}
            hidden={offset === null}
            top={offset && offset.top}
            left={offset && offset.left}
        >
            <StyleButton
                active={currentStyle.has('BOLD')}
                onToggle={() => onToggleInlineStyle('BOLD')}
            >
                <MaterialIcon name="FormatBold" />
            </StyleButton>
            <StyleButton
                active={currentStyle.has('ITALIC')}
                onToggle={() => onToggleInlineStyle('ITALIC')}
            >
                <MaterialIcon name="FormatItalic" />
            </StyleButton>
            <StyleButton
                active={currentStyle.has('UNDERLINE')}
                onToggle={() => onToggleInlineStyle('UNDERLINE')}
            >
                <MaterialIcon name="FormatUnderlined" />
            </StyleButton>
            <StyleButton
                active={'unordered-list-item' === blockType}
                onToggle={() => onToggleBlockType('unordered-list-item')}
            >
                <MaterialIcon name="FormatListBulleted" />
            </StyleButton>
            <Splitter />
            <StyleButton
                active={'header-one' === blockType}
                onToggle={() => onToggleBlockType('header-one')}
            >
                <MaterialIcon name="Title" fontSizeRem={2} />
            </StyleButton>
            <StyleButton
                active={'header-two' === blockType}
                onToggle={() => onToggleBlockType('header-two')}
            >
                <MaterialIcon name="Title" fontSizeRem={1.3} />
            </StyleButton>
            <StyleButton
                active={'blockquote' === blockType}
                onToggle={() => onToggleBlockType('blockquote')}
            >
                <MaterialIcon name="FormatQuote" />
            </StyleButton>
            <Splitter />
            <StyleButton onToggle={() => onAddInlineText()}>
                <MaterialIcon name="Functions" />
            </StyleButton>
        </Toolbar>
    );
};

export default TextToolbar;
