const mix = require('laravel-mix');

mix
    .setPublicPath('public')
    .version()
    .react()
    .copyDirectory('vendor/ckeditor/ckeditor', 'public/js/ckeditor')
    .copyDirectory('resources/assets/ckeditor/plugins', 'public/js/ckeditor/plugins')
    .copy('resources/assets/ckeditor/*.js', 'public/js/ckeditor')
    .copy('node_modules/@brightcove/player-loader/dist/brightcove-player-loader.min.js', 'public/build/js/videos')
    .copyDirectory('node_modules/cropperjs/dist', 'public/js/cropperjs')

    // Using a different public path + setResourceRoot breaks copyDirectory.
    // Each output is prefixed with 'build/' instead.
    .sass('resources/assets/entrypoints/admin.scss', 'build/css/admin.css')
    .js('resources/assets/entrypoints/admin.js', 'build/js/admin.js')
    .js('resources/assets/entrypoints/article.js', 'build/js/article.js')
    .sass('resources/assets/entrypoints/article.scss', 'build/css/article.css')
    .sass('resources/assets/entrypoints/article-plugin.scss', 'build/css/article-plugin.css')
    .js('resources/assets/entrypoints/bootstrap.js', 'build/js/bootstrap.js')
    .sass('resources/assets/entrypoints/ckeditor_popup.scss', 'build/css/ckeditor_popup.css')
    .sass('resources/assets/entrypoints/content_explorer_bootstrap.scss', 'build/css/content_explorer_bootstrap.css')
    .sass('resources/assets/entrypoints/error-page.scss', 'build/css/error-page.css')
    .sass('resources/assets/entrypoints/font-awesome.scss', 'build/css/font-awesome.css')
    .sass('resources/assets/entrypoints/h5p-admin.scss', 'build/css/h5p-admin.css')
    .sass('resources/assets/entrypoints/h5p-core.scss', 'build/css/h5p-core.css')
    .js('resources/assets/entrypoints/h5p-core-bundle.js', 'build/js/h5p-core-bundle.js')
    .sass('resources/assets/entrypoints/h5pcss.scss', 'build/css/h5pcss.css')
    .js('resources/assets/entrypoints/h5peditor-custom.js', 'build/js/h5peditor-custom.js')
    .js('resources/assets/entrypoints/h5peditor-image-popup.js', 'build/js/h5peditor-image-popup.js')
    .sass('resources/assets/entrypoints/h5picons.scss', 'build/css/h5picons.css')
    .js('resources/assets/entrypoints/h5pmetadata.js', 'build/js/h5pmetadata.js')
    .js('resources/assets/entrypoints/ndla-h5peditor-html.js', 'build/js/ndla-h5peditor-html.js')
    .sass('resources/assets/entrypoints/link.scss', 'build/css/link.css')
    .js('resources/assets/entrypoints/maxscore.js', 'build/js/maxscore.js')
    .js('resources/assets/entrypoints/metadata.js', 'build/js/metadata.js')
    .sass('resources/assets/entrypoints/ndlah5p-edit.scss', 'build/css/ndlah5p-edit.css')
    .sass('resources/assets/entrypoints/ndlah5p-editor.scss', 'build/css/ndlah5p-editor.css')
    .sass('resources/assets/entrypoints/ndlah5p-iframe.scss', 'build/css/ndlah5p-iframe.css')
    .sass('resources/assets/entrypoints/ndlah5p-iframe-legacy.scss', 'build/css/ndlah5p-iframe-legacy.css')
    .js('resources/assets/entrypoints/ndla-audio.js', 'build/js/ndla-audio.js')
    .js('resources/assets/entrypoints/ndla-image.js', 'build/js/ndla-image.js')
    .js('resources/assets/entrypoints/ndla-video.js', 'build/js/ndla-video.js')
    .js('resources/assets/entrypoints/react-article.js', 'build/js/react-article.js')
    .js('resources/assets/entrypoints/react-h5p.js', 'build/js/react-h5p.js')
    .js('resources/assets/entrypoints/react-questionset.js', 'build/js/react-questionset.js')
    .webpackConfig({
        resolve: {
            fallback: {
                // silence warnings about colors not being available
                os: false,
            }
        }
    })
;
