
const getTranslations = intl => {
    return {
        displayButtons: intl.formatMessage({ id: 'DISPLAYOPTIONS.DISPLAYBUTTONS' }),
        displayCopyright: intl.formatMessage({ id: 'DISPLAYOPTIONS.DISPLAYCOPYRIGHT' }),
        displayDownload: intl.formatMessage({ id: 'DISPLAYOPTIONS.DISPLAYDOWNLOAD' }),
        displayOptions: intl.formatMessage({ id: 'DISPLAYOPTIONS.DISPLAYOPTIONS' }),
    };
};

export default getTranslations;
