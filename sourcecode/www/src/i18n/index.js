import i18n from 'i18next';
import moment from 'moment';

// Also import moment locals when creating a new language
import 'moment/locale/nb';

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

i.on('languageChanged', (lng) => {
    moment.locale(lng);
});

export default i;
