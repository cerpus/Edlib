const {mix} = require('laravel-mix');
mix.disableNotifications();
/*
 mix.webpackConfig({
 entry: ["babel-polyfill", "resources/assets/js/app.js"]
 });
 */
let publicCss = 'public/css';
let publicJs = 'public/js';
let publicFonts = 'public/fonts';

//**********************
// Javascript
//**********************
mix.copy('vendor/ckeditor/ckeditor/ckeditor.js', publicJs + '/article-edit.js')
    .copy('vendor/ckeditor/ckeditor', publicJs + '/ckeditor', false)
    .copy('resources/assets/js', publicJs, false)
    .js([
        './vendor/components/jquery/jquery.min.js',
        './node_modules/bootstrap-sass/assets/javascripts/bootstrap.min.js'
    ], publicJs + '/bootstrap.js')
    .combine([
        'vendor/h5p/h5p-core/js/jquery.js',
        'vendor/h5p/h5p-core/js/h5p.js',
        'vendor/h5p/h5p-core/js/h5p-event-dispatcher.js',
        'vendor/h5p/h5p-core/js/h5p-x-api-event.js',
        'vendor/h5p/h5p-core/js/h5p-x-api.js',
        'vendor/h5p/h5p-core/js/h5p-content-type.js',
        'vendor/h5p/h5p-core/js/h5p-confirmation-dialog.js',
        'vendor/h5p/h5p-core/js/h5p-action-bar.js'
    ], publicJs + '/h5p-core.js')
    .js('resources/assets/js/react-vendor.js', publicJs + '/react-vendor.js')
    .js('resources/assets/js/react-components.js', publicJs + '/react-components.js')
    .js('resources/assets/js/app.js', publicJs + '/app.js')
    .js('resources/assets/js/admin.js', publicJs + '/admin.js')
    .version();

//**********************
// FONTS
//**********************
mix.copy(
    [
        'resources/assets/fonts',
        'node_modules/bootstrap-sass/assets/fonts/bootstrap',
        'node_modules/font-awesome/fonts',
        'vendor/h5p/h5p-core/fonts'
    ], publicFonts, false);


//**********************
// CSS
//**********************
mix.copy(
    [
        'node_modules/font-awesome/css/font-awesome.min.css',
        'resources/assets/css/cc-icons.css',
        'resources/assets/css/react-components.css'
    ],
    publicCss);

mix.sass('resources/assets/sass/bootstrap.scss', publicCss)
    .sass('resources/assets/sass/fake.scss', publicCss)
    .sass('resources/assets/sass/ckeditor_popup.scss', publicCss)
    .sass('resources/assets/sass/app.scss', publicCss)
    .sass('resources/assets/sass/admin.scss', publicCss)
    .sass('resources/assets/sass/article.scss', publicCss)
    .sass('resources/assets/sass/h5picons.scss', publicCss)
    .combine([
        'vendor/h5p/h5p-core/styles/h5p.css',
        'vendor/h5p/h5p-core/styles/h5p-admin.css',
        'vendor/h5p/h5p-core/styles/h5p-confirmation-dialog.css',
        'vendor/h5p/h5p-core/styles/h5p-core-button.css'
    ], publicCss + '/h5p-core.css')
    .version()
;
/*
 mix.version(
 [
 'public/css/react-components.css',
 'public/css/font-awesome.min.css',
 'public/css/cc-icons.css'
 ]
 )
 ;
 */


