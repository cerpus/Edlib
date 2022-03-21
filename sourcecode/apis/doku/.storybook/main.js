module.exports = {
  "stories": [
    "../resources/js/stories/**/*.stories.mdx",
    "../resources/js/stories/**/*.stories.@(js|jsx|ts|tsx)"
  ],
  "addons": [
    "@storybook/addon-links",
    "@storybook/addon-essentials",
    "@storybook/addon-interactions",
    {
      name: '@storybook/addon-postcss',
      options: {
        postcssLoaderOptions: {
          implementation: require('postcss'),
        },
      },
    },
  ],
  "framework": "@storybook/react",
  core: {
    builder: 'webpack5',
  },
  webpackFinal: async (config, { configType }) => {
      config.devtool = "eval-source-map";

      return config;
  },
}
