module.exports = {
    stories: ['../src/stories/**/*.stories.(js|mdx)'],
    addons: [
        '@storybook/addon-actions/register',
        '@storybook/addon-links/register',
        '@storybook/addon-viewport',
    ],
    core: {
        disableTelemetry: true,
        enableCrashReports: false,
    },
};
