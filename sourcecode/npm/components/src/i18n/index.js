import i18n from 'i18next';
import moment from 'moment';

// Also import moment locals when creating a new language
import 'moment/locale/nb';

const i = i18n.createInstance();

i.init({
    resources: {
        'en-gb': {
            translation: require('./en/translation'),
        },
        'nb-no': {
            translation: require('./nb/translation'),
        },
        'ko-kr': {
            translation: require('./ko/translation'),
        },
        'de-de': {
            translation: require('./de/translation'),
        },
        'es-es': {
            translation: require('./es/translation'),
        },
    },
    fallbackLng: 'en-gb',
    lng: 'en-gb',
    interpolation: {
        escapeValue: false,
    },
    react: {
        useSuspense: false,
    },
});

i.on('languageChanged', (lng) => {
    moment.locale(lng);
});

export default i;
