'use strict';

process.env.DISABLE_NOTIFIER = true;
const gulp = require("gulp");
const exec = require('child_process').exec;

gulp.task('default', () => {
    console.log('gulp is replaced by webpack');
    console.log('Available commands:');
    console.log('   npm run dev');
    console.log('   npm run watch');
    console.log('   npm run production');
    console.log('Starting production build...');
    exec('npm run production', function (err, stdout, stderr) {
        console.log(stdout);
        console.log(stderr);
    });
});


// process.env.DISABLE_NOTIFIER = true;
// var elixir = require('laravel-elixir');
//
// var gulp = require("gulp");
//
// elixir(function (mix) {
//
//     mix.scripts([
//         'jquery.js',
//         'h5p.js',
//         'h5p-event-dispatcher.js',
//         'h5p-x-api-event.js',
//         'h5p-x-api.js',
//         'h5p-content-type.js',
//         'h5p-confirmation-dialog.js',
//         'h5p-action-bar.js',
//     ], 'public/js/h5p-core.js', 'vendor/h5p/h5p-core/js')
//
//         .stylesIn('vendor/h5p/h5p-core/styles', 'public/css/h5p-core.css')
//
//         .copy(['vendor/h5p/h5p-core/fonts', 'node_modules/font-awesome/fonts/**', 'node_modules/bootstrap-sass/assets/fonts'], 'public/build/fonts')
//
//         .copy(['resources/assets/fonts/', 'node_modules/bootstrap-sass/assets/fonts/bootstrap'], 'public/fonts/')
//
//         .scripts([
//             './vendor/components/jquery/jquery.min.js',
//             './node_modules/bootstrap-sass/assets/javascripts/bootstrap.min.js'
//         ], 'public/js/bootstrap.js')
//
//         .copy('node_modules/font-awesome/css/font-awesome.min.css', 'public/css')
//
//         .copy('vendor/ckeditor/ckeditor/ckeditor.js', 'public/js/article-edit.js')
//
//         .sass(['bootstrap.scss', 'h5picons.scss'], 'public/css/content_explorer_bootstrap.css')
//
//         .sass('app.scss')
//         .sass('admin.scss')
//
//         .sass('ckeditor_popup.scss')
//
//         .sass('article.scss')
//
//         .sass('h5picons.scss', 'public/css/h5picons.css')
//
//         .copy('vendor/ckeditor/ckeditor', 'public/js/ckeditor')
//
//         .copy('node_modules/font-awesome/fonts', 'public/fonts')
//
//
//         .copy(['resources/assets/js/'], 'public/js/')
//
//         .webpack('admin.js')
//
//         .copy(['resources/assets/css/react-components.css'], 'public/css/')
//
//         .version([
//             'public/js/h5p-core.js',
//             'public/css/h5p-core.css',
//             'public/js/article-edit.js',
//             'public/css/app.css',
//             'public/css/admin.css',
//             'public/js/bootstrap.js',
//             'public/css/content_explorer_bootstrap.css',
//             'public/css/ckeditor_popup.css',
//             'public/css/article.css',
//             'public/css/font-awesome.min.css',
//             // React files
//             'public/js/app.js',
//             'public/js/admin.js',
//             'public/js/react-vendor.js',
//             'public/css/react-components.css',
//             'public/js/react-components.js'
//         ]);
// });
