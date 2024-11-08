import React from 'react';

const EditorSetupContext = React.createContext({
    adapterList: [],
    adapterName: null,
    canList: false,
    canPublish: false,
    contentProperties: null,
    creatorName: null,
    editorLanguage: '',
    enableUnsavedWarning: true,
    h5pLanguage: '',
    libraryUpgradeList: [],
    locked: false,
    lockedProperties: null,
    pulseUrl: '',
    showDisplayOptions: false,
    supportedTranslations: [],
    useLicense: false,
    userPublishEnabled: false,
});

export const EditorSetupProvider = ({
                                        adapterList,
                                        adapterName,
                                        canList,
                                        canPublish,
                                        contentProperties,
                                        creatorName,
                                        editorLanguage,
                                        enableUnsavedWarning,
                                        h5pLanguage,
                                        libraryUpgradeList,
                                        locked,
                                        lockedProperties,
                                        pulseUrl,
                                        showDisplayOptions,
                                        supportedTranslations,
                                        useLicense,
                                        userPublishEnabled,
                                        children,
}) => {
    return (
        <EditorSetupContext.Provider
            value={{
                adapterList,
                adapterName,
                canList,
                canPublish,
                contentProperties,
                creatorName,
                editorLanguage,
                enableUnsavedWarning,
                h5pLanguage,
                libraryUpgradeList,
                locked,
                lockedProperties,
                pulseUrl,
                showDisplayOptions,
                supportedTranslations,
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
