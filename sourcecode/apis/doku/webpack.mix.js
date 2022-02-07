const mix = require('laravel-mix');

mix
    .react()
    .ts('resources/js/app.tsx', 'public/js')
    .postCss('resources/css/app.css', 'public/css', [
        //
    ]);
