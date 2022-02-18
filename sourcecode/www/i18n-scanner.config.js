module.exports = {
    input: ['src/**/*.{js,jsx}', '!src/i18n/**', '!**/node_modules/**'],
    output: './src',
    options: {
        debug: true,
        func: {
            list: ['i18next.t', 'i18n.t', 't'],
            extensions: ['.js', '.jsx'],
        },
        trans: {
            component: 'Trans',
            i18nKey: 'i18nKey',
            defaultsKey: 'defaults',
            extensions: ['.js', '.jsx'],
            fallbackKey: function(ns, value) {
                return value;
            },
        },
        lngs: ['en', 'nb'],
        ns: ['translation'],
        defaultLng: 'en',
        defaultNs: 'translation',
        resource: {
            loadPath: 'src/i18n/{{lng}}/{{ns}}.json',
            savePath: 'i18n/{{lng}}/{{ns}}.json',
            jsonIndent: 4,
            lineEnding: '\n',
        },
        interpolation: {
            prefix: '{{',
            suffix: '}}',
        },
        nsSeparator: ':',
        keySeparator: '.',
    },
};
