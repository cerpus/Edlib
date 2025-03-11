import React from 'react';

const EditorSetupContext = React.createContext({
    adapterList: [],
    adapterName: null,
    canList: false,
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
});

export const EditorSetupProvider = ({
                                        adapterList,
                                        adapterName,
                                        canList,
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
                                        children,
}) => {
    return (
        <EditorSetupContext.Provider
            value={{
                adapterList,
                adapterName,
                canList,
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
           }}
        >
            {children}
        </EditorSetupContext.Provider>
    );
};

export const useEditorSetupContext = () =>
    React.useContext(EditorSetupContext);
