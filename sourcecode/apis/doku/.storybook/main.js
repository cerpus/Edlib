const path = require('path');
const toPath = (filePath) => path.join(process.cwd(), filePath);

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
  webpackFinal: async (config) => {
      return {
          ...config,
          devtool: 'eval-source-map',
          resolve: {
              ...config.resolve,
              alias: {
                  ...config.resolve.alias,
                  '@emotion/core': toPath('node_modules/@emotion/react'),
                  'emotion-theming': toPath('node_modules/@emotion/react'),
              },
          },
      };
  },
}
