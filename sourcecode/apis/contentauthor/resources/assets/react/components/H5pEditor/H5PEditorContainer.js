import React, { useState } from 'react';
import views from './views';
import List from './List';
import LanguagePicker from './LanguagePicker';
import H5P from '../H5P';
import { injectIntl } from 'react-intl';
import useH5PEditor from '../H5P/useH5PEditor';
import useConfirmWindowClose from './useConfirmWindowClose';
import { deepCopy, flattenPath, nextTick, removeKeys, set } from '../../utils/utils';
import FileUploadProgress from '../FileUploadProgress';
import Fade from '@material-ui/core/Fade';
import EditorContainer from '../EditorContainer/EditorContainer';
import { FormActions, useForm } from '../../contexts/FormContext';
import Sidebar, {
    AdapterSelector,
    DisplayOptions,
    ContentUpgradeContainer,
} from '../Sidebar';
import { getLanguageStringFromCode } from '../../utils/Helper';
import NewReleases from '@material-ui/icons/NewReleases';
import { useTheme } from '@material-ui/core/styles';
import { useEditorSetupContext } from '../../contexts/EditorSetupContext';
import getTextFields from "./List/getTextFields";

const H5PEditorContainer = ({ intl }) => {
    const {
        state: formState,
        state: { parameters: formParameters, max_score: maxScore },
        dispatch,
    } = useForm();
    const [currentView, setCurrentView] = React.useState(views.H5P);
    const [parameters, setParameters] = React.useState({
        ...formState,
        maxScore,
        parameters: JSON.parse(formParameters),
    });
    const startupParameters = React.useMemo(() => parameters, []);
    const [isSaved, setIsSaved] = React.useState(false);
    const [isH5PReady, setIsH5PReady] = React.useState(false);
    const h5PRef = React.createRef();
    const [fileStatus, setFileStatus] = React.useState({
        total: 10,
        inProgress: 0,
        failed: 0,
        done: 0,
    });
    const [showFileProgress, toggleShowFileProgress] = React.useState(false);
    const [librarySelected, setLibrarySelected] = useState(false);
    const theme = useTheme();
    const editorSetup = useEditorSetupContext();

    const onParamsChange = (newParameters) => {
        setParameters({
            ...parameters,
            ...newParameters,
        });
    };

    const {
        init,
        reDraw,
        getParams,
        getMaxScore,
        getLibrary,
        getTitle,
        h5pEditor,
        onBeforeUpgrade,
        stageUpgrade,
        iframeLoading,
        setAuthor,
    } = useH5PEditor(onParamsChange);

    const getCurrentParams = React.useCallback(() => {
        return currentView === views.H5P ? getParams() : parameters.parameters;
    }, [currentView, parameters, getParams]);

    const getLibraryCache = () =>
        h5pEditor && !iframeLoading
            ? h5pEditor.iframeWindow.H5PEditor.libraryCache
            : {};

    const shouldConfirmClose = React.useCallback(() => {
        if (isSaved) {
            return false;
        }

        const currentParams = getCurrentParams();

        return (
            JSON.stringify(
                removeKeys(startupParameters.parameters, ['metadata'])
            ) !== JSON.stringify(removeKeys(currentParams, ['metadata'])) ||
            currentParams.metadata.title !==
                startupParameters.parameters.metadata.title
        );
    }, [getCurrentParams, startupParameters, isSaved]);

    if (editorSetup.enableUnsavedWarning) {
        useConfirmWindowClose(shouldConfirmClose);
    }

    React.useEffect(() => {
        const H5PReadyInterval = setInterval(() => {
            if (typeof window.H5PEditor !== 'undefined') {
                clearInterval(H5PReadyInterval);
                setIsH5PReady(true);
            }
        }, 25);
    }, []);

    React.useEffect(() => {
        if (isH5PReady) {
            init(h5PRef.current, parameters);
        }
    }, [isH5PReady]);

    React.useEffect(() => {
        if (isH5PReady) {
            if (currentView === views.H5P) {
                reDraw(parameters.parameters, getLibrary());
            } else {
                onParamsChange({
                    parameters: getParams(),
                    library: getLibrary(),
                });
            }
        }
    }, [currentView, isH5PReady]);

    const getFormState = () => formState;

    React.useEffect(() => {
        if (h5pEditor) {
            const H5PLibraryInterval = setInterval(() => {
                try {
                    const selectedLibrary = getLibrary();
                    if (
                        typeof selectedLibrary === 'string' &&
                        selectedLibrary.length > 0
                    ) {
                        const { creatorName } = editorSetup;
                        if (creatorName !== null) {
                            setAuthor(creatorName, 'Author');
                        }
                        clearInterval(H5PLibraryInterval);
                        setLibrarySelected(true);
                    }
                    // eslint-disable-next-line no-empty
                } catch (ignore) {}
            }, 1000);
        }
    }, [h5pEditor, getCurrentParams, getFormState]);

    const save = (isDraft = false) => {
        try {
            const params = getCurrentParams();
            const { h5pLanguage } = editorSetup;
            if (
                !(params || params.params) ||
                typeof params.params === 'undefined'
            ) {
                return false;
            }

            //don't need the outcome. Simple test that it's a json object. The try/catch will catch if it fails
            JSON.parse(JSON.stringify(params));

            if (!getTitle()) {
                return false;
            }

            if (
                params.metadata &&
                h5pLanguage &&
                !params.metadata.defaultLanguage
            ) {
                params.metadata.defaultLanguage = h5pLanguage;
            }

            const formValues = getFormState();
            formValues.title = params.metadata.title || '';
            formValues.isDraft = isDraft;
            formValues.library = getLibrary();
            formValues.parameters = JSON.stringify(params);
            formValues.max_score = getMaxScore(params);

            setIsSaved(true);

            const errorHandler = ({ response }) => {
                let responseData;
                try {
                    responseData = JSON.parse(response.data);
                } catch (err) {
                    responseData = [response.request.responseText];
                }
                setIsSaved(false);
                return [responseData];
            };

            const statusHandler = (status) => {
                toggleShowFileProgress(true);
                setFileStatus(status);
                if (status.total === status.inProgress) {
                    toggleShowFileProgress(false);
                } else if (status.failed > 0) {
                    toggleShowFileProgress(false);
                    errorHandler({
                        responseText: intl.formatMessage({
                            id: 'H5P_EDITOR.UPLOAD_OF_MEDIAFILE_FAILED',
                        }),
                    });
                }
            };

            return {
                values: formValues,
                statusHandler,
                errorHandler,
                isValid: true,
            };
        } catch (error) {
            return {
                errorMessages: [error],
                isValid: false,
            };
        }
    };

    const getSidebarComponents = () => {
        const {
            adapterName = null,
            adapterList = [],
            showDisplayOptions = false,
        } = editorSetup;

        const {
            frame,
            copyright,
            download,
            language_iso_639_3: languageISO6393,
        } = formState;

        const components = [];
        if (showDisplayOptions === true) {
            components.push({
                id: 'displayOptions',
                title: intl.formatMessage({
                    id: 'DISPLAYOPTIONS.DISPLAYOPTIONS',
                }),
                component: (
                    <DisplayOptions
                        displayButtons={frame}
                        displayCopyright={copyright}
                        displayDownload={download}
                    />
                ),
            });
        }

        if (
            editorSetup.libraryUpgradeList &&
            editorSetup.libraryUpgradeList.length > 0
        ) {
            components.push({
                id: 'upgradeContent',
                title: intl.formatMessage({
                    id: 'H5PCONTENTUPGRADE.UPDATECONTENT',
                }),
                info: (
                    <NewReleases
                        htmlColor={theme.palette.tertiary.main}
                        fontSize="medium"
                    />
                ),
                component: (
                    <ContentUpgradeContainer
                        libraries={editorSetup.libraryUpgradeList}
                        onStageUpgrade={stageUpgrade}
                        onBeforeUpgrade={() =>
                            onBeforeUpgrade(getCurrentParams())
                        }
                    />
                ),
            });
        }

        const languageText = getLanguageStringFromCode(languageISO6393);
        components.push({
            id: 'language',
            title: intl.formatMessage({
                id: 'H5P_EDITOR.LANGUAGE_PICKER.LANGUAGE',
            }),
            info: languageText !== null ? <div>({languageText})</div> : null,
            component: (
                <LanguagePicker
                    language={languageISO6393}
                    onChange={(language) => {
                        dispatch({ type: FormActions.setLanguage, payload: { language } });
                    }}
                    onGetFields={async () => {
                        const params = getParams();
                        const fields = await getTextFields(params, parameters.library, getLibraryCache());

                        const translationFields = fields.map(field => ({
                            path: 'params.' + flattenPath(field.path),
                            value: field.originalValue,
                        }));
                        // Also translate the title
                        translationFields.push(
                            {
                                path: "metadata.title",
                                value: params.metadata.title,
                            },
                            {
                                path: "metadata.extraTitle",
                                value: params.metadata.extraTitle,
                            }
                        );
                        return translationFields;
                    }}
                    onSetFields={(fields) => {
                        const newParameters = deepCopy(getParams());
                        for (const i in fields) {
                            set(newParameters, i, fields[i]);
                        }
                        onParamsChange(newParameters);

                        dispatch({ type: FormActions.setIsNewLanguageVariant, payload: {
                            isNewLanguageVariant: true,
                        }});

                        nextTick(() => {
                            if (currentView === views.H5P) {
                                reDraw(newParameters, parameters.library);
                            } else {
                                setCurrentView(null);
                                setCurrentView(views.LIST);
                            }
                        });
                    }}
                    supportedLanguages={editorSetup.supportedTranslations}
                />
            ),
        });

        if (adapterName !== null) {
            components.push({
                id: 'adapterSelect',
                title: 'Adapter',
                info: <div>({adapterName})</div>,
                component: (
                    <AdapterSelector
                        current={adapterName}
                        adapters={adapterList}
                    />
                ),
            });
        }

        return components;
    };

    function getContainerTabs() {
        const tabs = [
            {
                label: intl.formatMessage({
                    id: 'H5P_EDITOR.TAB.H5P_VIEW',
                }),
                value: views.H5P,
            },
        ];

        if (librarySelected) {
            tabs.push(
                {
                    label: intl.formatMessage({
                        id: 'H5P_EDITOR.TAB.LIST_VIEW',
                    }),
                    value: views.LIST,
                }
            );
        }

        return  tabs;
    }

    return (
        <EditorContainer
            tabs={getContainerTabs()}
            activeTab={currentView}
            onTabChange={setCurrentView}
            sidebar={
                librarySelected === true && (
                    <Fade in={librarySelected}>
                        <Sidebar
                            customComponents={getSidebarComponents()}
                            onSave={save}
                        />
                    </Fade>
                )
            }
            containerClassname="h5p-container"
        >
            <div className="h5p-editor-container">
                {currentView === views.LIST && (
                    <List
                        parameters={parameters}
                        onUpdate={setParameters}
                        startupParameters={startupParameters}
                        libraryCache={getLibraryCache}
                    />
                )}
                <H5P visible={currentView === views.H5P} ref={h5PRef} />
                <FileUploadProgress
                    total={fileStatus.total}
                    inProgress={fileStatus.inProgress}
                    done={fileStatus.done}
                    show={showFileProgress}
                />
            </div>
        </EditorContainer>
    );
};

H5PEditorContainer.propTypes = {
};

export default injectIntl(H5PEditorContainer);
