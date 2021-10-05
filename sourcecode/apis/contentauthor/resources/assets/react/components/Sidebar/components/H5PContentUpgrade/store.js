const contentUpgradeStore = (state, action) => {
    switch (action.type) {
        case actions.readyForUpgrades:
            return {
                ...state,
                readyForUpgrade: true,
            };
        case actions.handleStartUpgrade: {
            const {
                selectedVersion,
            } = action.payload;
            return {
                ...state,
                confirmationShow: true,
                libraryId: selectedVersion,
                selectedVersion: state.libraries.filter(library => {
                    // eslint-disable-next-line radix
                    return library.id.toString() === selectedVersion;
                }).shift(),
            };
        }
        case actions.toggleConfirm: {
            const libraryId = state.confirmationShow ? '' : state.libraryId;
            return {
                ...state,
                libraryId,
                confirmationShow: !state.confirmationShow,
            };
        }
        case actions.handleConfirm: {
            const {
                originalParameters,
                originalLibrary,
            } = action.payload;
            return {
                ...state,
                confirmationShow: false,
                inProgress: true,
                percentComplete: 0,
                originalParameters,
                originalLibrary,
            };
        }
        case actions.upgradeComplete:
            return {
                ...state,
                upgradeComplete: true,
                percentComplete: 100,
            };
        case actions.undoUpgrade:
            return {
                ...state,
                libraryId: '',
                upgradeComplete: false,
                inProgress: false,
            };
        case actions.updateProgress:
            return {
                ...state,
                percentComplete: action.payload.percentComplete,
            };
        default:
            return state;
    }
};

const actions = {
    readyForUpgrades: 'READY',
    handleStartUpgrade: 'STARTUPGRADE',
    toggleConfirm: 'TOGGLE_CONFIRM',
    handleConfirm: 'UPGRADE_CONFIRM',
    upgradeComplete: 'UPGRADE_COMPLETE',
    undoUpgrade: 'UNDO_UPGRADE',
    updateProgress: 'UPDATE_PROGRESS',
};

export {
    contentUpgradeStore as default,
    actions,
} ;
