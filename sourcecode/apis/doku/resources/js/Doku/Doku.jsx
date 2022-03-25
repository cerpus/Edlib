import React from 'react';
import { EditorState, RichUtils, convertFromRaw, convertToRaw } from 'draft-js';
import { DokuContext } from './dokuContext';
import styled from 'styled-components';
import MathJax from './components/MathJax';
import handleDrop from './draftJSHelpers/handleDrop';
import Editor from './components/Editor';
import useSize from './hooks/useSize';
import addTex from './draftJSHelpers/addTex';
import MathModal from './components/MathModal';
import AddResource from './components/AddResource';
import EditorStyles from './components/EditorStyles';
import addAtomicBlock from './draftJSHelpers/addAtomicBlock';
import { ThemeProvider, useTheme } from '@cerpus/ui';
import TextToolbar from './components/TextToolbar';
import { focusableBlocksStore } from './plugins/Focusable';
import EditResourceModal from './components/EditResourceModal';
import useTranslation from './hooks/useTranslation';

const DokuWrapper = styled.div`
    position: relative;
    padding: 10px 20px 10px 70px;
    max-width: 100%;
    width: 850px;
    margin: 0 auto;
    line-height: 1.58;
`;

const Doku = ({ editorState, setEditorState, usersForLti = null }) => {
    const editorRef = React.useRef();
    const wrapperRef = React.useRef();
    const [subEditorHasFocus, setSubEditorHasFocus] = React.useState(false);
    const [mathEditor, setMathEditor] = React.useState(false);
    const [updateEdlibResourceData, setEditEdlibResourceData] = React.useState(
        null
    );
    const wrapperSize = useSize(wrapperRef);
    const isEditing = !!setEditorState;
    const theme = useTheme();
    const { t } = useTranslation();

    const focus = () => {
        if (isEditing) {
            editorRef.current.focus();
        }
    };

    return (
        <ThemeProvider
            theme={{
                ...theme,
                doku: {
                    defaultSpacing: 24,
                },
            }}
        >
            <DokuContext
                value={{
                    focusableBlocksStore,
                    editorState,
                    setEditorState,
                    isEditing,
                    usersForLti,
                    onBlockUpdateData: (entityKey, data) => {
                        if (!isEditing) return;

                        let newContentState = convertFromRaw(
                            convertToRaw(
                                editorState
                                    .getCurrentContent()
                                    .replaceEntityData(entityKey, data)
                            )
                        );

                        const es = EditorState.push(
                            editorState,
                            newContentState,
                            'change-block-data'
                        );

                        setEditorState(es);
                    },
                    subEditorHasFocus,
                    setSubEditorHasFocus,
                    isMobile: wrapperSize.width < 650,
                    wrapperSize: wrapperSize,
                    openMathModal: (entityKey) =>
                        setMathEditor({
                            entityKey,
                            data: editorState
                                .getCurrentContent()
                                .getEntity(entityKey)
                                .getData(),
                        }),
                    isBlockSelected: (blockKey) => {
                        const selection = editorState.getSelection();
                        if (
                            selection.getAnchorKey() !== selection.getFocusKey()
                        ) {
                            return false;
                        }
                        const content = editorState.getCurrentContent();
                        const selectedBlock = content.getBlockForKey(
                            selection.getAnchorKey()
                        );
                        return blockKey === selectedBlock.getKey();
                    },
                    setEditEdlibResourceData,
                }}
            >
                <MathJax.Provider>
                    <DokuWrapper>
                        <EditorStyles
                            isEditing={isEditing}
                            onClick={focus}
                            ref={wrapperRef}
                        >
                            <Editor
                                readOnly={subEditorHasFocus || !setEditorState}
                                onClick={focus}
                                ref={editorRef}
                                editorState={editorState}
                                setEditorState={setEditorState}
                                handleDrop={(...args) =>
                                    handleDrop(
                                        ...args,
                                        editorState,
                                        setEditorState
                                    )
                                }
                                placeholder={t('Your text here')}
                                focusableBlocksStore={focusableBlocksStore}
                            />
                            {isEditing && (
                                <TextToolbar
                                    editorState={editorState}
                                    editorRef={editorRef}
                                    onToggleBlockType={(blockType) =>
                                        setEditorState(
                                            RichUtils.toggleBlockType(
                                                editorState,
                                                blockType
                                            )
                                        )
                                    }
                                    onToggleInlineStyle={(inlineStyle) =>
                                        setEditorState(
                                            RichUtils.toggleInlineStyle(
                                                editorState,
                                                inlineStyle
                                            )
                                        )
                                    }
                                    onAddInlineText={() =>
                                        setEditorState(
                                            addTex(editorState, false)
                                        )
                                    }
                                />
                            )}
                        </EditorStyles>
                        <div style={{ clear: 'both' }} />
                        {isEditing && (
                            <AddResource
                                offsetTop={
                                    wrapperRef.current &&
                                    wrapperRef.current.offsetTop
                                }
                                onAddResource={(
                                    type,
                                    edlibId,
                                    data,
                                    moveSelectionToEnd
                                ) => {
                                    setEditorState(
                                        addAtomicBlock(
                                            editorState,
                                            type,
                                            {
                                                ...data,
                                                edlibId,
                                            },
                                            moveSelectionToEnd
                                        )
                                    );
                                }}
                            />
                        )}
                    </DokuWrapper>
                    <MathModal
                        isOpen={!!mathEditor}
                        onClose={() => setMathEditor(false)}
                        currentValue={mathEditor ? mathEditor.data : {}}
                        onInsert={(value) => {
                            if (mathEditor && mathEditor.entityKey) {
                                setEditorState(
                                    EditorState.set(editorState, {
                                        currentContent: editorState
                                            .getCurrentContent()
                                            .replaceEntityData(
                                                mathEditor.entityKey,
                                                {
                                                    tex: value,
                                                }
                                            ),
                                    })
                                );
                            }
                            setMathEditor(false);
                        }}
                    />
                    <EditResourceModal
                        updateEdlibResourceData={updateEdlibResourceData}
                        onClose={() => setEditEdlibResourceData(false)}
                    />
                </MathJax.Provider>
            </DokuContext>
        </ThemeProvider>
    );
};

export default Doku;
