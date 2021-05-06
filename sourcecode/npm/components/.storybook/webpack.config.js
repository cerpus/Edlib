const path = require('path');
const buildCssRules = require('treply-scripts/config/buildCssRules');

module.exports = async ({ config }) => {
    return {
        ...config,
        resolve: {
            ...config.resolve,
            alias: {
                ...config.resolve.alias,
                ['styled-components']: path.resolve(
                    __dirname,
                    '../node_modules/styled-components'
                ),
                ['@material-ui/styles']: path.resolve(
                    __dirname,
                    '../node_modules/@material-ui/styles'
                ),
                ['react-router-dom']: path.resolve(
                    __dirname,
                    '../node_modules/react-router-dom'
                ),
            },
        },
        module: {
            ...config.module,
            rules: [...config.module.rules, ...buildCssRules(false)],
        },
    };
};
