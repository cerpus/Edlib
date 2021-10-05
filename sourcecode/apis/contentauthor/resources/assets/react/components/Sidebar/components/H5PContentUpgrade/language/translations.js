
const getTranslations = intl => {
    return {
        version: intl.formatMessage({ id: 'H5PCONTENTUPGRADE.VERSION' }),
        upgrade: intl.formatMessage({ id: 'H5PCONTENTUPGRADE.UPGRADE' }),
        confirmation: intl.formatMessage({ id: 'H5PCONTENTUPGRADE.CONFIRMATION' }),
        yes: intl.formatMessage({ id: 'H5PCONTENTUPGRADE.YES' }),
        no: intl.formatMessage({ id: 'H5PCONTENTUPGRADE.NO' }),
        upgradeConfirmation: intl.formatMessage({ id: 'H5PCONTENTUPGRADE.UPGRADE-CONFIRMATION' }),
        progress: intl.formatMessage({ id: 'H5PCONTENTUPGRADE.PROGRESS' }),
        undoText: intl.formatMessage({ id: 'H5PCONTENTUPGRADE.UNDO-TEXT' }),
        undo: intl.formatMessage({ id: 'H5PCONTENTUPGRADE.UNDO' }),
        noUpgradesAvailable: intl.formatMessage({ id: 'H5PCONTENTUPGRADE.NOUPGRADESAVAILABLE' }),
        selectVersion: intl.formatMessage({ id: 'H5PCONTENTUPGRADE.SELECTVERSION' }),
        updateContent: intl.formatMessage({ id: 'H5PCONTENTUPGRADE.UPDATECONTENT' }),
    };
};

export default getTranslations;
