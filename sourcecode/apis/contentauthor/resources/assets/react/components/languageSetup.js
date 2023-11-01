import defaultMessages from '../language/en.json';

const loadLocale = async (locale) => {
    if (!locale) {
        locale = document.documentElement.lang || window.navigator.language;
    }

    if (locale === 'en') {
        return {locale: 'en', messages: defaultMessages};
    }

    let messages;
    try {
        ({ default: messages } = await import(`../language/${locale}.json`));
    } catch (e) {
        if (locale.includes('_')) {
            locale = locale.split('_')[0];

            try {
                ({ messages } = await loadLocale(locale));
            } catch (e) {
                locale = 'en';
                messages = defaultMessages;
            }
        }
    }

    if (locale !== 'en') {
        messages = Object.assign({}, defaultMessages, messages);
    }

    return {locale, messages, defaultLocale: 'en'};
};

export { loadLocale };
