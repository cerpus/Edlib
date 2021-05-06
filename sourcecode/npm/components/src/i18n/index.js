import i18n from 'i18next/index';

const i = i18n.createInstance();

i.init({
    resources: {
        en: {
            translation: require('./en/translation'),
        },
        nb: {
            translation: require('./nb/translation'),
        },
    },
    fallbackLng: 'nb',
    lng: 'nb',
    interpolation: {
        escapeValue: false,
    },
    react: {
        useSuspense: false,
    },
});

export default i;
