module.exports = {
    extends: ['react-app'],
    plugins: [
        'import', // eslint-plugin-import plugin. https://www.npmjs.com/package/eslint-plugin-import
    ],
    rules: {
        'import/order': [
            'warn',
            {
                alphabetize: {
                    caseInsensitive: true,
                    order: 'asc',
                },
                groups: [
                    'builtin',
                    'external',
                    'index',
                    'sibling',
                    'parent',
                    'internal',
                ],
            },
        ],
        'sort-imports': [
            'warn',
            {
                ignoreCase: false,
                ignoreDeclarationSort: true,
                ignoreMemberSort: false,
            },
        ],
    },
};
