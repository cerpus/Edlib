import React from 'react';

const context = React.createContext({
    editorState: null,
    setEditorState: null,
    usersForLti: null,
    isEditing: false,
    isMobile: false,
    wrapperSize: null,
    subEditorHasFocus: false,
    focusableBlocksStore: null,
    onBlockUpdateData: () => {},
    setSubEditorHasFocus: () => {},
    openMathModal: () => {},
    isBlockSelected: () => {},
    setEditEdlibResourceData: () => {},
    openImageModal: () => {},
});

export const DokuContext = context.Provider;

export const useDokuContext = () => React.useContext(context);
