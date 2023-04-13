import defaultMessages from '../language/en.json';

/**
 * @param {string} [locale]
 * @return Promise<{locale: string, messages: object}>
 */
const loadLocale = async (locale) => {
    if (!locale) {
        locale = document.documentElement.lang || window.navigator.language;
    }

    if (locale === 'en') {
        return { locale: 'en', messages: defaultMessages };
    }

    locale = locale.replace('-', '_');

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

    return { locale, messages };
};

export { loadLocale };
