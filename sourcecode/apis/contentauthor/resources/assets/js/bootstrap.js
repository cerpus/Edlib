(async function () {
    if (!window.jQuery) {
        window.jQuery = window.$ = await import('jquery');
    }

    await import('bootstrap-sass/assets/javascripts/bootstrap');
})();
