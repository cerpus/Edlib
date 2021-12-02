(async function () {
    if (!window.jQuery) {
        const { default: jQuery } = await import('jquery');
        window.jQuery = window.$ = jQuery;
    }

    await import('bootstrap-sass/assets/javascripts/bootstrap');
})();
