import i18n from 'i18next';
import moment from 'moment';

const i = i18n.createInstance();

i.init({
    compatibilityJSON: 'v3',
    interpolation: {
        escapeValue: false,
    },
    lng: 'en',
    fallbackLng: 'en',
    react: {
        useSuspense: false,
    },
    supportedLngs: ['en', 'nb', 'ko', 'de', 'es', 'nn',],
    resources: {
        en: {
            translation: require('./en/translation'),
        },
        nb: {
            translation: require('./nb/translation'),
        },
        ko: {
            translation: require('./ko/translation'),
        },
        de: {
            translation: require('./de/translation'),
        },
        es: {
            translation: require('./es/translation'),
        },
        nn: {
            translation: require('./nn/translation'),
        },
    },
});

i.on('languageChanged', (lng) => {
    moment.locale(lng);
});

export default i;
