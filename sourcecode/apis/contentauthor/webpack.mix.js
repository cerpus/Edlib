const mix = require('laravel-mix');

mix
    .setPublicPath('public')
    .version()
    .react()
    .copyDirectory('vendor/ckeditor/ckeditor', 'public/js/ckeditor')
    .copyDirectory('resources/assets/ckeditor/plugins', 'public/js/ckeditor/plugins')
    .copy('resources/assets/ckeditor/*.js', 'public/js/ckeditor')
    .copy('node_modules/@brightcove/player-loader/dist/brightcove-player-loader.min.js', 'public/js/videos')
    .copyDirectory('node_modules/cropperjs/dist', 'public/js/cropperjs')
    .sass('resources/assets/entrypoints/admin.scss', 'css/admin.css')
    .js('resources/assets/entrypoints/admin.js', 'js/admin.js')
    .js('resources/assets/entrypoints/article.js', 'js/article.js')
    .sass('resources/assets/entrypoints/article.scss', 'css/article.css')
    .sass('resources/assets/entrypoints/article-plugin.scss', 'css/article-plugin.css')
    .js('resources/assets/entrypoints/bootstrap.js', 'js/bootstrap.js')
    .sass('resources/assets/entrypoints/ckeditor_popup.scss', 'css/ckeditor_popup.css')
    .sass('resources/assets/entrypoints/content_explorer_bootstrap.scss', 'css/content_explorer_bootstrap.css')
    .sass('resources/assets/entrypoints/error-page.scss', 'css/error-page.css')
    .sass('resources/assets/entrypoints/font-awesome.scss', 'css/font-awesome.css')
    .sass('resources/assets/entrypoints/h5p-admin.scss', 'css/h5p-admin.css')
    .sass('resources/assets/entrypoints/h5p-core.scss', 'css/h5p-core.css')
    .js('resources/assets/entrypoints/h5p-core-bundle.js', 'js/h5p-core-bundle.js')
    .sass('resources/assets/entrypoints/h5pcss.scss', 'css/h5pcss.css')
    .js('resources/assets/entrypoints/h5peditor-custom.js', 'js/h5peditor-custom.js')
    .js('resources/assets/entrypoints/h5peditor-image-popup.js', 'js/h5peditor-image-popup.js')
    .sass('resources/assets/entrypoints/h5picons.scss', 'css/h5picons.css')
    .js('resources/assets/entrypoints/h5pmetadata.js', 'js/h5pmetadata.js')
    .js('resources/assets/entrypoints/ndla-h5peditor-html.js', 'js/ndla-h5peditor-html.js')
    .sass('resources/assets/entrypoints/link.scss', 'css/link.css')
    .js('resources/assets/entrypoints/maxscore.js', 'js/maxscore.js')
    .js('resources/assets/entrypoints/metadata.js', 'js/metadata.js')
    .sass('resources/assets/entrypoints/ndlah5p-edit.scss', 'css/ndlah5p-edit.css')
    .sass('resources/assets/entrypoints/ndlah5p-editor.scss', 'css/ndlah5p-editor.css')
    .sass('resources/assets/entrypoints/ndlah5p-iframe.scss', 'css/ndlah5p-iframe.css')
    .sass('resources/assets/entrypoints/ndlah5p-iframe-legacy.scss', 'css/ndlah5p-iframe-legacy.css')
    .js('resources/assets/entrypoints/ndla-audio.js', 'js/ndla-audio.js')
    .js('resources/assets/entrypoints/ndla-image.js', 'js/ndla-image.js')
    .js('resources/assets/entrypoints/ndla-video.js', 'js/ndla-video.js')
    .js('resources/assets/entrypoints/react-article.js', 'js/react-article.js')
    .js('resources/assets/entrypoints/react-h5p.js', 'js/react-h5p.js')
    .js('resources/assets/entrypoints/react-questionset.js', 'js/react-questionset.js')
    .webpackConfig({
        resolve: {
            fallback: {
                // silence warnings about colors not being available
                os: false,
            }
        }
    })
;
