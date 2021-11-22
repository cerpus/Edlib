/*eslint-env node*/
/*eslint-disable no-console*/

const webpack = require('webpack');
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CleanObsoleteChunks = require('webpack-clean-obsolete-chunks');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const ManifestPlugin = require('webpack-manifest-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const moment = require('moment');

module.exports = (env) => {
    const inProduction = (env === 'production');

    const watchOptions = {
        aggregateTimeout: 500,
        ignored: /node_modules/,
    };

    if (env === 'poll') {
        watchOptions.poll = 1000;
    }

    const statsConfig = {
        chunks: true,
        colors: !inProduction,
        performance: !inProduction,
        timings: !inProduction,
        version: true,

        assets: false,
        children: false,
        chunkModules: false,
        chunkOrigins: false,
        depth: false,
        entrypoints: false,
        hash: false,
        modules: false,
        providedExports: false,
        source: false,
        usedExports: false,
    };

    const defaultConfig = {
        mode: env,
        stats: statsConfig,
        context: path.resolve(__dirname),
        watchOptions: watchOptions,
        devtool: inProduction ? false : 'cheap-module-source-map',
        parallelism: 4,
        performance: { hints: false },
        optimization: {
            minimize: inProduction,
            minimizer: [
                new TerserPlugin({
                    test: /\.js(\?.*)?$/i,
                    parallel: true,
                    sourceMap: false,
                    terserOptions: {
                        compress: true,
                        mangle: true,
                    },
                }),
            ].filter(Boolean),
        },
        // resolve: {
        //     alias: {
        //         '@cerpus/ui': path.resolve(__dirname, 'node_modules/@cerpus/ui'),
        //         '@cerpus/edlib-components': path.resolve(__dirname, 'node_modules/@cerpus/edlib-components'),
        //         'styled-components': path.resolve(__dirname, 'node_modules/styled-components'),
        //         react: path.resolve(__dirname, 'node_modules/react'),
        //         'react-dom': path.resolve(__dirname, 'node_modules/react-dom'),
        //         'react-router-dom': path.resolve(__dirname, 'node_modules/react-router-dom'),
        //         'react-popper': path.resolve(__dirname, 'node_modules/react-popper'),
        //         'color': path.resolve(__dirname, 'node_modules/color')
        //     },
        //     modules: [
        //         path.resolve(__dirname, 'resources/assets/react/components'),
        //         path.resolve(__dirname, 'resources/assets/react'),
        //         'node_modules',
        //     ],
        //},
    };

    const cssRules = [
        {
            test: /\.css$/,
            use: [
                MiniCssExtractPlugin.loader,
                {
                    loader: 'css-loader',
                },
            ].filter(Boolean),
        },
        {
            test: /\.s[ac]ss$/,
            use: [
                MiniCssExtractPlugin.loader,
                {
                    loader: 'css-loader', // translates CSS into CommonJS
                }, {
                    loader: 'sass-loader', // compiles Sass to CSS
                },
            ],
        },
    ];

    // Builds the manifest file (mapping between filename and versioned filename)
    const manifestCache = {};
    const manifestConfig = {
        publicPath: '/',
        fileName: 'mix-manifest.json',
        basePath: '/',
        seed: manifestCache,
        filter: (file) => !file.name.startsWith('/build/'),
    };

    const miniCssExtract = new MiniCssExtractPlugin({
        filename: 'build/css/[name]-[chunkhash].css',
        allChunks: true,
        ignoreOrder: true,
    });

    const unversionedCss = new MiniCssExtractPlugin({
        filename: 'build/css/[name].css',
        allChunks: true,
        ignoreOrder: true,
    });

    const fontsRule = {
        test: /\.(svg|eot|ttf|woff|woff2)$/,
        use: [
            {
                loader: 'file-loader',
                options: {
                    name: 'build/fonts/[name].[ext]',
                    esModule: false,
                },
            },
        ],
    };

    const reactBuild = {
        ...defaultConfig,
        resolve: {
            modules: [
                path.resolve(__dirname, 'resources/assets/react/components'),
                path.resolve(__dirname, 'resources/assets/react'),
                'node_modules',
            ],
        },
        entry: {
            'react-h5p': [
                './resources/assets/react/h5p.js',
                './resources/assets/sass/app.scss',
                './resources/assets/css/cc-icons2.css',
            ],
            'react-article': [
                './resources/assets/react/article.js',
                './resources/assets/sass/app.scss',
                './resources/assets/css/cc-icons2.css',
            ],
            'react-questionset': [
                './resources/assets/react/questionset.js',
                './resources/assets/sass/app.scss',
                './resources/assets/css/cc-icons2.css',
            ],
            'react-embed': [
                './resources/assets/react/embed.js',
                './resources/assets/sass/app.scss',
                './resources/assets/css/cc-icons2.css',
            ],
            'react-contentbrowser': [
                './resources/assets/react/contentBrowser.js',
            ],
        },
        output: {
            filename: 'build/js/[name]-[chunkhash].js',
            path: path.resolve(process.cwd(), 'public'),
            publicPath: '/',
        },
        optimization: {
            ...defaultConfig.optimization,
            namedChunks: true,
            splitChunks: {
                cacheGroups: {
                    'react-vendor': {
                        name: 'react-vendor',
                        chunks: chunk => [
                            'react-h5p',
                            'react-article',
                            'react-questionset',
                            'react-embed',
                        ].includes(chunk.name),
                        test: function minChunks(module) {
                            return module.context && module.context.indexOf('node_modules') !== -1;
                        },
                    },
                    'react-components': {
                        name: 'react-components',
                        chunks: chunk => [
                            'react-h5p',
                            'react-article',
                            'react-questionset',
                            'react-embed',
                        ].includes(chunk.name),
                        test: function minChunks(module) {
                            return module.context && (
                                module.context.indexOf('react/components') !== -1 ||
                                module.context.indexOf('react/library') !== -1
                            );
                        },
                    },
                },
            },
        },
        module: {
            rules: [
                {
                    test: /\.js$/,
                    exclude: path.resolve(__dirname, 'node_modules'),
                    loader: 'babel-loader',
                },
                ...cssRules,
                fontsRule,
            ],
        },
        plugins: [
            //new webpack.ProgressPlugin(),
            // There is no automatic deletion of old builds, so this removes all versioned css and js files
            // Images and fonts are not versioned and will be overwritten
            new CleanWebpackPlugin({
                cleanStaleWebpackAssets: false,
                verbose: !inProduction,
                cleanOnceBeforeBuildPatterns: [
                    'build/{js,css}/react-components-*.{js,css,map}',
                    'build/{js,css}/react-vendor-*.{js,css,map}',
                    'build/{js,css}/react-{h5p,article,questionset,embed}-*.{js,css,map}',
                    'build/{js,css}/react-contentbrowser-*.{js,css,map}',
                ],
            }),
            // Removes old builds when using watch
            new CleanObsoleteChunks(),
            new webpack.DefinePlugin({
                'process.env': {
                    'NODE_ENV': (inProduction ? JSON.stringify('production') : JSON.stringify('develop')),
                },
            }),
            miniCssExtract,
            new ManifestPlugin(manifestConfig),
            function logBuildTime() {
                this.plugin('watch-run', (watching, callback) => {
                    console.log('Project: reactBuild');
                    console.log('Build start: ' + moment().format('YYYY-MM-DD HH:mm:ss'));
                    callback();
                });
            },
        ],
    };

    const contentAuthor = {
        ...defaultConfig,
        entry: {
            'admin': [
                './resources/assets/js/admin.js',
            ],
            'maxscore': [
                './resources/assets/js/bulk-maxscore.js',
            ],
            'metadata': [
                './resources/assets/js/admin/metadata.js',
            ],
            'bootstrap': [
                './vendor/components/jquery/jquery.min.js',
                'bootstrap-loader',
            ],
            'jwtclient': [
                './resources/assets/js/jwtclient.js',
                './resources/assets/js/jsrequestintercept.js',
            ],
            'font-awesome': [
                'font-awesome/css/font-awesome.css',
            ],
            'content_explorer_bootstrap': [
                'bootstrap-loader',
                './resources/assets/sass/h5picons.scss',
            ],
            'ckeditor_popup': [
                './resources/assets/sass/ckeditor_popup.scss',
            ],
            'article': [
                './resources/assets/sass/front.scss',
                './resources/assets/sass/article.scss',
                './resources/assets/sass/ndlaarticle.scss',
                './resources/assets/js/article-view.js',
                './resources/assets/js/article-xapi.js',
            ],
            'h5p-core-bundle': [
                './vendor/h5p/h5p-core/js/jquery.js',
                './vendor/h5p/h5p-core/js/h5p.js',
                './vendor/h5p/h5p-core/js/h5p-event-dispatcher.js',
                './vendor/h5p/h5p-core/js/h5p-x-api-event.js',
                './vendor/h5p/h5p-core/js/h5p-x-api.js',
                './vendor/h5p/h5p-core/js/h5p-content-type.js',
                './vendor/h5p/h5p-core/js/h5p-confirmation-dialog.js',
                './resources/assets/js/h5p/request-queue.js', //TODO Change to vanilla H5P when they fix
                './vendor/h5p/h5p-core/js/h5p-action-bar.js',
            ],
            'link': [
                './resources/assets/sass/front.scss',
                './resources/assets/sass/link.scss',
            ],
            'h5pcss': [
                './resources/assets/sass/front.scss',
                './resources/assets/sass/h5p.scss',
            ],
            'front': [
                './resources/assets/sass/front.scss',
            ],
            'game': [
                './resources/assets/sass/front.scss',
            ],
            'ndla-contentbrowser': [
                './resources/assets/js/h5p/ndla-contentbrowser.js',
            ],
            'h5peditor-custom': [
                './resources/assets/js/h5p/h5peditor-custom.js',
            ],
            'h5pmetadata': [
                './resources/assets/js/h5p/h5peditor-metadata-author-widget.js',
                './resources/assets/js/h5p/h5peditor-metadata.js',
                './resources/assets/js/h5p/h5peditor-number.js',
                './resources/assets/js/h5p/h5peditor-select.js',
                './resources/assets/js/h5p/h5peditor-text.js',
                './resources/assets/js/h5p/h5peditor-textarea.js',
                './resources/assets/js/h5p/h5peditor-list.js',
                './resources/assets/js/h5p/h5peditor-list-editor.js',
            ],
            'h5p/h5peditor-image-popup': [
                './resources/assets/js/h5p/h5peditor-image-popup.js',
            ],
            'h5p-core': [
                './vendor/h5p/h5p-core/styles/h5p.css',
                './vendor/h5p/h5p-core/styles/h5p-confirmation-dialog.css',
                './vendor/h5p/h5p-core/styles/h5p-core-button.css',
            ],
            'h5p-admin': [
                './vendor/h5p/h5p-core/styles/h5p-admin.css',
            ],
            'h5picons': [
                './resources/assets/sass/h5picons.scss',
            ],
        },
        output: {
            filename: 'build/js/[name]-[chunkhash].js',
            path: path.resolve(__dirname, 'public'),
            publicPath: '/',
        },
        module: {
            rules: [
                ...cssRules,
                fontsRule,
                {
                    test: /\.js$/,
                    loader: 'babel-loader',
                    query: {
                        presets: ['@babel/preset-env'],
                        plugins: [
                            'transform-object-assign',
                            'transform-class-properties',
                        ],
                    },
                },
                {
                    test: /\.(png|gif)$/,
                    use: [
                        {
                            loader: 'file-loader',
                            options: {
                                name: 'build/css/images/[name].[ext]',
                                publicPath: '../',
                                esModule: false,
                            },
                        },
                    ],
                },
            ],
        },
        plugins: [
            // There is no automatic deletion of old builds, so this removes all versioned css and js files
            // Images and fonts are not versioned and will be overwritten
            new CleanWebpackPlugin({
                cleanStaleWebpackAssets: false,
                verbose: !inProduction,
                cleanOnceBeforeBuildPatterns: [
                    'build/{js,css}/admin-*.{js,css,map}',
                    'build/{js,css}/admin/metadata-*.{js,css,map}',
                    'build/{js,css}/bulk-maxscore-*.{js,css,map}',
                    'build/{js,css}/bootstrap-*.{js,css,map}',
                    'build/{js,css}/font-awesome-*.{js,css,map}',
                    'build/{js,css}/content_explorer_bootstrap-*.{js,css,map}',
                    'build/{js,css}/ckeditor_popup-*.{js,css,map}',
                    'build/{js,css}/article-*.{js,css,map}',
                    '!build/{js,css}/article-plugin.{js,css,map}',
                    'build/{js,css}/h5pcss-*.{js,css,map}',
                    'build/{js,css}/h5p/h5peditor-*.{js,css,map}',
                    'build/{js,css}/ndla-contentbrowser-*.{js,css,map}',
                    'build/{js,css}/h5p-editor-*.{js,css,map}',
                    'build/{js,css}/link-editor-*.{js,css,map}',
                    'build/{js,css}/link-*.{js,css,map}',
                    'build/{js,css}/question-editor-*.{js,css,map}',
                    'build/{js,css}/jwtclient-*.{js,css,map}',
                    'build/{js,css}/mathquill.min-*.{js,css,map}',
                    'build/{js,css}/h5p-core-*.{js,css,map}',
                    'build/{js,css}/h5p-admin-*.{js,css,map}',
                    'build/{js,css}/h5picons-*.{js,css,map}',
                    'build/{js,css}/game-*.{js,css,map}',
                    'build/{js,css}/metadata-*.{js,css,map}',
                    'build/{js,css}/h5pmetadata-*.{js,css,map}',
                    'build/{js,css}/maxscore-*.{js,css,map}',
                    'build/{js,css}/h5peditor-custom-*.{js,css,map}',
                    'build/{js,css}/h5p/*.{js,css,map}',
                    'build/{js,css}/front-*.{js,css,map}',
                ],
            }),
            // Removes old builds when using watch
            new CleanObsoleteChunks(),
            new webpack.DefinePlugin({
                'process.env': {
                    'NODE_ENV': (inProduction ? JSON.stringify('production') : JSON.stringify('develop')),
                },
            }),
            new CopyWebpackPlugin([
                {
                    context: './vendor/ckeditor',
                    from: '**',
                    to: 'build/js',
                    ignore: [
                        'config.js',
                    ],
                },
                {
                    context: './resources/assets/js/ckeditor',
                    from: '**',
                    to: 'build/js/ckeditor',
                },
                {
                    context: './resources/assets/js',
                    from: 'cerpus.js',
                    to: 'build/js',
                },
                {
                    context: './resources/assets/js',
                    from: 'resource_common.js',
                    to: 'build/js/resource-common.js',
                },
                {
                    context: './resources/assets/js',
                    from: 'article-xapi.js',
                    to: 'build/js',
                },
                {
                    context: './resources/assets/js',
                    from: 'h5p-editor.js',
                    to: 'build/js',
                },
                {
                    context: './resources/assets/js/',
                    from: 'listener.js',
                    to: 'build/js',
                },
                {
                    context: './resources/assets/js/',
                    from: 'jwtclient.js',
                    to: 'build/js',

                },
                {
                    context: './resources/assets/js/videos',
                    from: '**',
                    to: 'build/js/videos',

                },
                {
                    context: './resources/assets/js/h5p',
                    from: '**',
                    to: 'build/js/h5p',
                },
                {
                    context: './resources/assets/js',
                    from: 'editor-setup.js',
                    to: 'build/js',
                },
                {
                    context: './resources/assets/js',
                    from: 'question-editor.js',
                    to: 'build/js',
                },
                {
                    context: './node_modules/@brightcove/player-loader/dist',
                    from: 'brightcove-player-loader.min.js',
                    to: 'build/js/videos',
                },
                {
                    context: './node_modules/cropperjs/dist',
                    from: '**',
                    to: 'build/js/cropperjs',
                },
                {
                    context: './resources/assets/js/mathquillEditor/lib',
                    from: '**',
                    to: 'build/js/mathquillEditor',
                },
                {
                    context: './resources/assets/graphical',
                    from: '**',
                    to: 'build/graphical',
                },
            ]),
            miniCssExtract,
            new ManifestPlugin(manifestConfig),
            function logBuildTime() {
                this.plugin('watch-run', (watching, callback) => {
                    console.log('Project: contentAuthor');
                    console.log('Build start: ' + moment().format('YYYY-MM-DD HH:mm:ss'));
                    callback();
                });
            },
        ],
    };

    const articleCss = {
        ...defaultConfig,
        entry: {
            'article-plugin': [
                './resources/assets/sass/article.scss',
                './resources/assets/sass/ndlaarticle.scss',
            ],
        },
        output: {
            filename: 'build/css/[name]-[chunkhash].css',
            path: path.resolve(process.cwd(), 'public'),
            publicPath: '/',
        },
        module: {
            rules: [
                ...cssRules,
            ],
        },
        plugins: [
            // There is no automatic deletion of old builds, so this removes all versioned css and js files
            // Images and fonts are not versioned and will be overwritten
            new CleanWebpackPlugin({
                cleanStaleWebpackAssets: false,
                verbose: !inProduction,
                cleanOnceBeforeBuildPatterns: [
                    'build/{js,css}/article-plugin.{js,css}',
                    'build/{js,css}/article-plugin-*.{js,css}',
                ],
            }),
            // Removes old builds when using watch
            new CleanObsoleteChunks(),
            new webpack.DefinePlugin({
                'process.env': {
                    'NODE_ENV': (inProduction ? JSON.stringify('production') : JSON.stringify('develop')),
                },
            }),
            unversionedCss,
            new ManifestPlugin(manifestConfig),
            function logBuildTime() {
                this.plugin('watch-run', (watching, callback) => {
                    console.log('Project: articleCss');
                    console.log('Build start: ' + moment().format('YYYY-MM-DD HH:mm:ss'));
                    callback();
                });
            },
        ],
    };

    const adminCss = {
        ...defaultConfig,
        entry: {
            'Admin': [
                './resources/assets/sass/admin.scss',
            ],
        },
        output: {
            filename: 'build/css/[name].css',
            path: path.resolve(__dirname, 'public'),
            publicPath: '/',
        },
        module: {
            rules: cssRules,
        },
        plugins: [
            // There is no automatic deletion of old builds, so this removes all versioned css and js files
            // Images and fonts are not versioned and will be overwritten
            new CleanWebpackPlugin({
                cleanStaleWebpackAssets: false,
                verbose: !inProduction,
                cleanOnceBeforeBuildPatterns: [
                    'build/{js,css}/Admin-*.{js,css}',
                ],
            }),
            // Removes old builds when using watch
            new CleanObsoleteChunks(),
            new webpack.DefinePlugin({
                'process.env': {
                    'NODE_ENV': (inProduction ? JSON.stringify('production') : JSON.stringify('develop')),
                },
            }),
            miniCssExtract,
            new ManifestPlugin(manifestConfig),
            function logBuildTime() {
                this.plugin('watch-run', (watching, callback) => {
                    console.log('Project: adminCss');
                    console.log('Build start: ' + moment().format('YYYY-MM-DD HH:mm:ss'));
                    callback();
                });
            },
        ],
    };

    const ndlaH5pCustomCss = {
        ...defaultConfig,
        entry: {
            'ndlah5p-iframe': [
                './resources/assets/css/ndla-h5p-custom/ndla-h5p-iframe.css',
            ],
            'ndlah5p-iframe-legacy': [
                './resources/assets/css/ndla-h5p-custom/ndla-h5p.css',
            ],
            'ndlah5p-edit': [
                './resources/assets/css/ndla-h5p-custom/ndla-h5p-edit.css',
            ],
            'ndlah5p-editor': [
                './resources/assets/css/ndla-h5p-custom/ndla-h5p-editor.css',
            ],
        },
        output: {
            filename: 'build/js/[name].js',
            path: path.resolve(process.cwd(), 'public'),
            publicPath: '/',
        },
        module: {
            rules: cssRules,
        },
        plugins: [
            // There is no automatic deletion of old builds, so this removes all versioned css and js files
            // Images and fonts are not versioned and will be overwritten
            new CleanWebpackPlugin({
                cleanStaleWebpackAssets: false,
                verbose: !inProduction,
                cleanOnceBeforeBuildPatterns: [
                    'build/{js,css}/ndlah5p-iframe*.{js,css,map}',
                    'build/{js,css}/ndlah5p-edit*.{js,css,map}',
                ],
            }),
            // Removes old builds when using watch
            new CleanObsoleteChunks(),
            new webpack.DefinePlugin({
                'process.env': {
                    'NODE_ENV': (inProduction ? JSON.stringify('production') : JSON.stringify('develop')),
                },
            }),
            miniCssExtract,
            new ManifestPlugin(manifestConfig),
            function logBuildTime() {
                this.plugin('watch-run', (watching, callback) => {
                    console.log('Project: ndlaH5pCustomCss');
                    console.log('Build start: ' + moment().format('YYYY-MM-DD HH:mm:ss'));
                    callback();
                });
            },
        ],
    };

    return [
        reactBuild,
        contentAuthor,
        articleCss,
        ndlaH5pCustomCss,
        adminCss,
    ];
};
