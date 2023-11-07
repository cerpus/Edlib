import defaultMessages from '../language/en.json';

const loadLocale = async (locale) => {
    if (!locale) {
        locale = document.documentElement.lang || window.navigator.language;
    }

    if (locale === 'en') {
        return {locale: 'en', messages: defaultMessages};
    }
    if (locale.includes('_')) {
        locale = locale.replace('_', '-');
    }

    let messages;
    try {
        ({ default: messages } = await import(`../language/${locale}.json`));
    } catch (e) {
        if (locale.includes('-')) {
            // Check for messages using the ISO 639-1 code, but keep the original code to avoid incorrect date/time format
            // E.g. 'en' messages are used for all 'en-xx' locales without own translation, but using 'en' as locale will use US date/time formats
            const tempLocale = locale.split('-')[0];

            try {
                ({ messages } = await loadLocale(tempLocale));
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
