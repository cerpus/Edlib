import i18n from 'i18next';

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
    fallbackLng: 'en',
    lng: 'en',
    interpolation: {
        escapeValue: false,
    },
    react: {
        useSuspense: false,
    },
});

export default i;
