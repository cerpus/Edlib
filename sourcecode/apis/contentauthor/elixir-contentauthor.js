'use strict';
var gulp = require('gulp');
var stream = require('vinyl-source-stream'); // Used to stream bundle for further handling
var browserify = require('browserify');
var watchify = require('watchify');
var babelify = require('babelify');
var gulpif = require('gulp-if');
var uglify = require('gulp-uglify');
var streamify = require('gulp-streamify');
var concat = require('gulp-concat');
var cleancss = require('gulp-clean-css');
var gutil = require('gulp-util');
var glob = require('glob');
var shell = require('gulp-shell');
var rm = require('gulp-rm');

//The root target for 'develop' build
var TARGETFOLDER = './resources/assets';

//The root target for 'jsdoc' build
var JSDOCSOURCE = './docs-src';

// Local components to exclude when building Main
var LocalComponents = [];

// External dependencies, bundled to 'vendor.js' and excluded from Components and Main
var VENDORDEPENDENCIES = [
    'intl',
    'react',
    'react-dom',
    'react-intl',
    'react-bootstrap',
    'babel-polyfill',
    'react-radio-group',
    'react-intl/locale-data/nb',
    'react-intl/locale-data/en',
    'react-intl/locale-data/sv'
];

// External CSS, bundled to 'vendor.css'
var VENDORCSS = [
];

gulp.task('default', [
    'develop-vendor',
    'develop-vendor-css',
    'develop-main',
    'develop-css'
]);

gulp.task('deploy', [
    'deploy-vendor',
    'deploy-vendor-css',
    'deploy-main',
    'deploy-css'
]);

// Main task for building JS Doc.
// JSDoc is fed the compiled files in docs-src because it fails when it encounters JSX, the components
// are built into separate files, keeping their names.
gulp.task('jsdoc', [
    'jsdoc-clean-docs',
    'jsdoc-clean-docs-src',
    'jsdoc-components'
], shell.task([
    './node_modules/.bin/jsdoc -c jsdoc.json',
    'rm -R docs-src'
]));

// The 'builder' tasks used by the main tasks.
// They are split up so that we can enforce dependencies between tasks and make sure they are built synchronously.

gulp.task('jsdoc-clean-docs', function () {
    return (gulp.src('./docs/**', {read: false}).pipe(rm()));
});

gulp.task('jsdoc-clean-docs-src', function () {
    return (gulp.src('./docs-src/**', {read: false}).pipe(rm()));
});

gulp.task('develop-vendor', function (cb) {
    vendorBuilder(CONFIG.develop.vendor, cb);
});

gulp.task('develop-vendor-css', function (cb) {
    cssBuilder(CONFIG.develop.vendorCSS, cb);
});

gulp.task('develop-components', function (cb) {
    componentBuilder(CONFIG.develop.components, cb);
});

gulp.task('develop-main', ['develop-components'], function (cb) {
    mainBuilder(CONFIG.develop.main, cb);
});

gulp.task('develop-css', ['develop-components'], function (cb) {
    cssBuilder(CONFIG.develop.CSS, cb);
});

gulp.task('deploy-vendor', function (cb) {
    global.process.env.NODE_ENV = 'production';
    vendorBuilder(CONFIG.deploy.vendor, cb);
});

gulp.task('deploy-vendor-css', function (cb) {
    cssBuilder(CONFIG.deploy.vendorCSS, cb);
});

gulp.task('deploy-components', function (cb) {
    componentBuilder(CONFIG.deploy.components, cb);
});

gulp.task('deploy-main', ['deploy-components'], function (cb) {
    mainBuilder(CONFIG.deploy.main, cb);
});

gulp.task('deploy-css', ['deploy-components'], function (cb) {
    cssBuilder(CONFIG.deploy.CSS, cb);
});

gulp.task('jsdoc-components', function (cb) {
    componentBuilderForJsDoc(CONFIG.jsdoc.components, cb);
});

// Bundles the VENDORDEPENDENCIES to vendor.js
function vendorBuilder (options, cb) {
    var builder = browserify({
        debug: false,
        require: VENDORDEPENDENCIES
    });

    return builder.bundle()
        .on('error', gutil.log)
        .pipe(stream('react-vendor.js'))
        .pipe(gulpif(!options.development, streamify(uglify().on('error', gutil.log))))
        .pipe(gulp.dest(options.destination))
        .pipe(gutil.buffer(function () {
            cb();
        }));
}

// Bundles the Main app into main.js and excludes the VENDORDEPENDENCIES and LocalComponents
function mainBuilder (options, cb) {
    var cbCalled = false;
    var mainBuilder = browserify({
        entries: [
            options.root + options.name + '.js'
        ],
        debug: options.development, // Gives us source mapping
        transform: [
            babelify.configure({
                presets: [
                    'latest',
                    'react'
                ],
                plugins: [
                    "transform-class-properties",
                    'transform-object-assign'
                ]
            })
        ],
        cache: {},
        packageCache: {},
        fullPaths: false // Requirement of watchify,
    });

    //Do not include the vendor files, they are in a separate package
    mainBuilder.external(VENDORDEPENDENCIES);

    // and the local components in another separate package
    LocalComponents.forEach(function (cmp) {
        mainBuilder.external(cmp.expose);
    });

    var mainBundler = function () {
        var start = Date.now();
        mainBuilder.bundle()
            .on('error', gutil.log)
            .pipe(stream(options.targetFilename))
            .pipe(gulpif(!options.development, streamify(uglify().on('error', gutil.log))))
            .pipe(gulp.dest(options.target))
            .pipe(gutil.buffer(function () {
                if (cbCalled === false) {
                    cb();
                    cbCalled = true;
                } else {
                    gutil.log('Rebuilt \'' + gutil.colors.green('Main') + '\' in ' + (Date.now() - start) + 'ms');
                }
            }));
    };

    if (options.watch) {
        mainBuilder = watchify(mainBuilder);
        mainBuilder.on('update', mainBundler);
        gutil.log('Watching \'' + gutil.colors.green('Main') + '\'');
    }
    return mainBundler();
}

// Bundles the Components and adds them to the LocalComponents array so that Main app does not include them
function componentBuilder (options, cb) {
    var languageFiles = glob.sync('*.js', {cwd: options.languageRoot});
    var libraryFiles = glob.sync('*.js', {cwd: options.libraryRoot});
    var cbCalled = false;

    var componentsLang = {};

    glob.sync('*/', {cwd: options.componentsRoot}).forEach(function (folder) {
        var name = folder.split('/')[0];
        LocalComponents.push({
            expose: './components/' + name, // The name to use in require(), should start with ./ to indicate local module
            src: options.componentsRoot + folder + name + '.js'
        });
        glob.sync('*.js', {cwd: options.componentsRoot + folder + 'language/'}).forEach(function (langFile) {
            var lang = langFile.split('.')[0];
            if (typeof(componentsLang[lang]) === 'undefined') {
                componentsLang[lang] = {messages: {}};
            }
            var msgs = require(options.componentsRoot + folder + 'language/' + langFile).messages;
            componentsLang[lang].messages = Object.assign(msgs, componentsLang[lang].messages);
        });
    });

    languageFiles.forEach(function (file) {
        var lang = file.split('.')[0];
        var content = require(options.languageRoot + file);
        content.messages = Object.assign({}, (componentsLang[lang] ? componentsLang[lang].messages : {}), content.messages);
        var fake = new gutil.File({
            path: '/temp/' + file,
            contents: new Buffer('module.exports = ' + JSON.stringify(content) + ';')
        });
        LocalComponents.push({
            expose: './language/' + lang, // The name to use in require(), should start with ./ to indicate local module
            src: fake
        });
    });

    libraryFiles.forEach(function (file) {
        LocalComponents.push({
            expose: './library/' + file.split('.')[0], // The name to use in require(), should start with ./ to indicate local module
            src: options.libraryRoot + file
        });
    });

    var componentsBuilder = browserify({
        debug: options.development, // Gives us source mapping
        transform: [babelify.configure({
            presets: [
                'latest',
                'react'
            ],
            plugins: [
                "transform-class-properties",
                'transform-object-assign'
            ]
        })],
        cache: {},
        packageCache: {},
        fullPaths: false // Requirement of watchify,
    });

    // The files in this package
    LocalComponents.forEach(function (cmp) {
        componentsBuilder.require(cmp.src, {expose: cmp.expose, basedir: '.'});
        gutil.log('  - ' + gutil.colors.yellow(cmp.expose) + gutil.colors.gray(' (' + cmp.src + ')'));
    });

    //Do not include the vendor files, they are in a separate package
    componentsBuilder.external(VENDORDEPENDENCIES);

    var componentsBundler = function () {
        var start = Date.now();
        componentsBuilder.bundle()
            .on('error', gutil.log)
            .pipe(stream('react-components.js'))
            .pipe(gulpif(!options.development, streamify(uglify().on('error', gutil.log))))
            .pipe(gulp.dest(options.componentsTarget))
            .pipe(gutil.buffer(function () {
                if (cbCalled === false) {
                    cb();
                    cbCalled = true;
                } else {
                    gutil.log('Rebuilt \'' + gutil.colors.green('Components') + '\' in ' + (Date.now() - start) + 'ms');
                }
            }));
    };

    if (options.watch) {
        componentsBuilder = watchify(componentsBuilder);
        componentsBuilder.on('update', componentsBundler);
        gutil.log('Watching \'' + gutil.colors.green('Components') + '\'');
    }
    return componentsBundler();
}

// Build each component to a separate file, also build config
function componentBuilderForJsDoc (options, cb) {
    var componentFolders = glob.sync('**/*.js', {cwd: options.componentsRoot});
    var libraryFiles = glob.sync('*.js' , {cwd: options.libraryRoot});
    var componentCount = 0;

    libraryFiles.forEach(function (file) {
        var name = file.split('.')[0];
        LocalComponents.push({
            expose: './library/' + name, // The name to use in require(), should start with ./ to indicate local module
            src: options.libraryRoot + file,
            targetFileName: file
        });
        componentCount++;
    });

    // Add the rest of the components
    componentFolders.forEach(function (file) {
        LocalComponents.push({
            expose: './components/' + file.split('.')[0], // The name to use in require(), should start with ./ to indicate local module
            src: options.componentsRoot + file,
            targetFileName: file
        });
        componentCount++;
    });

    LocalComponents.forEach(function (cmp) {
        gutil.log('  - ' + cmp.expose);
        var componentsBuilder = browserify({
            debug: false, // Gives us source mapping
            transform: [
                babelify.configure({
                    presets: [
                        'latest',
                        'react'
                    ],
                    plugins: [
                        "transform-class-properties",
                        'transform-object-assign'
                    ]
                })
            ],
            cache: {},
            packageCache: {},
            fullPaths: false // Requirement of watchify,
        });

        var src = (cmp.src.substr(-3) === '.js' ? cmp.src : cmp.src + '.js');
        componentsBuilder.require(src, {expose: cmp.expose});

        //Do not include the vendor files, they are in a separate package
        componentsBuilder.external(VENDORDEPENDENCIES);

        // The files in this package
        LocalComponents.forEach(function (component) {
            if (component.expose !== cmp.expose) {
                componentsBuilder.external(component.expose);
            }
        });

        var componentsBundler = function () {
            componentsBuilder.bundle()
                .on('error', gutil.log)
                .pipe(stream(cmp.targetFileName))
                .pipe(gulp.dest(options.componentsTarget))
                .pipe(gutil.buffer(function () {
                    componentCount--;
                    if (componentCount === 0) {
                        cb();
                    }
                }));
        };
        componentsBundler();
    });
}

// Depending on the options it either
// 1. Bundles the VENDORCSS files into vendor.css
// 2. Bundles Component and Common CSS into app.css
function cssBuilder (options, cb) {
    var files = [];

    if (!options.src) {
        files.push('./resources/assets/css/cc-icons.css');

        // First the components CSS
        glob.sync('**/*.css', {cwd: options.components}).forEach(function (file) {
            files.push(options.components + file);
        });
        // Then the application CSS located in the react root folder
        glob.sync('*.css', {cwd: options.root}).forEach(function (file) {
            if (files.indexOf(options.root + file) === -1) {
                files.push(options.root + file);
            }
        });
    } else {
        files = [].concat(options.src);
    }

    if (options.development) {
        var cbCalled = false;
        var run = function () {
            var start = Date.now();
            gulp.src(files)
                .pipe(concat(options.target))
                .pipe(gulp.dest(options.destination))
                .pipe(gutil.buffer(function () {
                    if (cbCalled === false) {
                        cb();
                        cbCalled = true;
                    } else {
                        gutil.log('Rebuilt \'' + gutil.colors.green(options.name) + '\' in ' + (Date.now() - start) + 'ms');
                    }
                }));
        };
        if (options.watch) {
            gulp.watch(files, run);
            gutil.log('Watching \'' + gutil.colors.green(options.name) + '\'');
        }
        run();
    } else {
        gulp.src(files)
            .pipe(concat(options.target))
            .pipe(cleancss())
            .pipe(gulp.dest(options.destination));
    }
}

// Config for the different builds and parts
var CONFIG = {
    develop: {
        vendor: {
            destination: TARGETFOLDER + '/js',
            development: true
        },
        vendorCSS: {
            name: 'Vendor CSS',
            src: VENDORCSS,
            destination: TARGETFOLDER + '/css',
            target: 'react-vendor.css',
            development: true
        },
        components: {
            languageRoot: './resources/assets/react/language/',
            componentsRoot: './resources/assets/react/components/',
            libraryRoot: './resources/assets/react/library/',
            componentsTarget: TARGETFOLDER + '/js',
            development: true,
            watch: true
        },
        main: {
            root: './resources/assets/react/',
            name: 'app',
            target: TARGETFOLDER + '/js',
            targetFilename: 'app.js',
            development: true,
            watch: true
        },
        CSS: {
            name: 'App CSS',
            root: './resources/assests/react/',
            components: './resources/assets/react/components/',
            destination: TARGETFOLDER + '/css',
            target: 'react-components.css',
            development: true,
            watch: true
        }
    },
    deploy: {
        vendor: {
            destination: TARGETFOLDER + '/js',
            development: false
        },
        vendorCSS: {
            name: 'Vendor CSS',
            src: VENDORCSS,
            destination: TARGETFOLDER + '/css',
            target: 'react-vendor.css',
            development: false
        },
        components: {
            languageRoot: './resources/assets/react/language/',
            componentsRoot: './resources/assets/react/components/',
            libraryRoot: './resources/assets/react/library/',
            componentsTarget: TARGETFOLDER + '/js',
            development: false,
            watch: false
        },
        main: {
            root: './resources/assets/react/',
            name: 'app',
            target: TARGETFOLDER + '/js',
            targetFilename: 'app.js',
            development: false,
            watch: false
        },
        CSS: {
            name: 'App CSS',
            root: './resources/assests/react/',
            components: './resources/assets/react/components/',
            destination: TARGETFOLDER + '/css',
            target: 'react-components.css',
            development: false,
            watch: false
        }
    },
    jsdoc: {
        components: {
            componentsRoot: './resources/assets/react/components/',
            libraryRoot: './resources/assets/react/library/',
            componentsTarget: JSDOCSOURCE,
            development: true
        },
        main: {
            root: './src/',
            name: 'coursecreator',
            target: JSDOCSOURCE,
            targetFilename: 'coursecreator.js',
            development: true
        }
    }
};
