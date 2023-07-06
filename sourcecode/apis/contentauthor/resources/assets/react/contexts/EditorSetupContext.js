import React from 'react';

const EditorSetupContext = React.createContext({
    autoTranslateTo: '',
    canList: false,
    canPublish: false,
    contentProperties: null,
    creatorName: null,
    editorLanguage: '',
    h5pLanguage: '',
    hideNewVariant: false,
    libraryUpgradeList: [],
    locked: false,
    lockedProperties: null,
    pulseUrl: '',
    showDisplayOptions: false,
    useLicense: false,
    userPublishEnabled: false,
});

export const EditorSetupProvider = ({
                                        autoTranslateTo,
                                        canList,
                                        canPublish,
                                        contentProperties,
                                        creatorName,
                                        editorLanguage,
                                        h5pLanguage,
                                        hideNewVariant,
                                        libraryUpgradeList,
                                        locked,
                                        lockedProperties,
                                        pulseUrl,
                                        showDisplayOptions,
                                        useLicense,
                                        userPublishEnabled,
                                        children,
}) => {
    return (
        <EditorSetupContext.Provider
            value={{
                autoTranslateTo,
                canList,
                canPublish,
                contentProperties,
                creatorName,
                editorLanguage,
                h5pLanguage,
                hideNewVariant,
                libraryUpgradeList,
                locked,
                lockedProperties,
                pulseUrl,
                showDisplayOptions,
                useLicense,
                userPublishEnabled,
           }}
        >
            {children}
        </EditorSetupContext.Provider>
    );
};

export const useEditorSetupContext = () =>
    React.useContext(EditorSetupContext);
