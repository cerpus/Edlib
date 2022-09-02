import React, { useEffect, useMemo, useReducer } from 'react';
import { FormattedMessage, injectIntl } from 'react-intl';
import ContentUpgradeLayout, { ContentNoUpgrades } from './ContentUpgradeLayout';
import PropTypes from 'prop-types';
import store, { actions } from './store';
import getTranslations from './language/translations';

const initialState = {
    selectedVersion: null,
    confirmationShow: false,
    upgradeComplete: false,
    originalLibrary: null,
    originalParameters: {},
    readyForUpgrade: false,
    inProgress: false,
    percentComplete: 0,
    libraries: [],
    libraryId: '',
};

const ContentUpgradeContainer = ({
    libraries = [],
    intl,
    onBeforeUpgrade,
    onStageUpgrade,
    initIframeEditor,
    getIframeEditor,
}) => {
    const translations = useMemo(() => {
        const translations = getTranslations(intl);
        translations.undoTextHTML = (
            <FormattedMessage
                id="H5PCONTENTUPGRADE.UNDO-TEXT"
                values={{
                    nl: <br/>,
                }}
            />
        );
        return translations;
    }, [intl]);

    const [state, dispatch] = useReducer(store, null, () => {
        return Object.assign({}, initialState, { libraries });
    });

    if (libraries.length === 0) {
        return (
            <ContentNoUpgrades
                noUpgradeAvailable={translations.noUpgradesAvailable}
            />
        );
    }

    const handleUpgradeReady = () => {
        dispatch({ type: actions.readyForUpgrades, payload: true });
    };

    const handleStartUpgrade = event => {
        dispatch({
            type: actions.handleStartUpgrade,
            payload: {
                selectedVersion: event.target.value,
                confirmationShow: true,
            },
        });
    };

    const handleToggleConfirmModal = () => {
        dispatch({ type: actions.toggleConfirm });
    };

    const handleConfirmUpgrade = () => {
        const _existingValues = onBeforeUpgrade();
        dispatch({
            type: actions.handleConfirm,
            payload: {
                originalParameters: _existingValues.params,
                originalLibrary: _existingValues.library,
            },
        });
    };

    const handleUpgradeComplete = (contentParams) => {
        onStageUpgrade(state.selectedVersion.name, contentParams);
        dispatch({ type: actions.upgradeComplete });
    };

    const handleUndoUpgrade = () => {
        onStageUpgrade(state.originalLibrary, state.originalParameters);
        dispatch({ type: actions.undoUpgrade });
    };

    useEffect(() => {
        initIframeEditor(handleUpgradeReady);
    }, []);

    useEffect(() => {
        if (state.inProgress === true && state.percentComplete === 0) {
            const iframeEditor = getIframeEditor();
            const library = new iframeEditor.ContentType(state.originalLibrary);
            const upgradeLibrary = iframeEditor.ContentType.getPossibleUpgrade(library, libraries.filter(library => state.selectedVersion.id === library.id));

            iframeEditor.upgradeContent(library, upgradeLibrary, JSON.parse(state.originalParameters), (err, result) => {
                if (err) {
                    // eslint-disable-next-line no-undef
                    onError(err);
                } else {
                    handleUpgradeComplete(result);
                }
            });
        }
    }, [state]);

    return (
        <ContentUpgradeLayout
            onClick={handleStartUpgrade}
            libraries={libraries}
            showConfirm={state.confirmationShow}
            onConfirm={handleConfirmUpgrade}
            upgradeComplete={state.upgradeComplete}
            onToggleConfirm={handleToggleConfirmModal}
            onUndoUpgrade={handleUndoUpgrade}
            percentProgress={state.percentComplete}
            inProgress={state.inProgress}
            translations={translations}
            readyForUpgrade={state.readyForUpgrade}
            selectedLibraryId={state.libraryId}
        />
    );
};

ContentUpgradeContainer.defaultProps = {
    libraries: [],
    // eslint-disable-next-line no-undef
    onBeforeUpgrade: () => H5PEditor.beforeUpgrade(),
    // eslint-disable-next-line no-undef
    onStageUpgrade: (library, params) => H5PEditor.stageUpgrade(library, params),
    initIframeEditor: (callback) => {
        function checkIfReady() {
            return typeof window.IframeH5PEditor !== 'undefined';
        }

        if (checkIfReady()) {
            return callback();
        }

        let attempts = 0;
        const loaderInterval = setInterval(() => {
            if (checkIfReady() === true || attempts >= 20) {
                clearInterval(loaderInterval);
                callback();
            }
            attempts++;
        }, 100);
    },
    getIframeEditor: () => window.IframeH5PEditor,
};

ContentUpgradeContainer.propTypes = {
    libraries: PropTypes.array,
    onBeforeUpgrade: PropTypes.func,
    onStageUpgrade: PropTypes.func,
    onError: PropTypes.func,
    initIframeEditor: PropTypes.func,
    getIframeEditor: PropTypes.func,
};

export default injectIntl(ContentUpgradeContainer);
