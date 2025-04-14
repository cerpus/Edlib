// From h5p-core/js/h5p.js
//
// Changes:
// 1. Remove handling for obsolete browsers.
// 2. Delays 'entered' events until fullscreen transition is (likely) completed

H5P.fullScreen = function ($element, instance, exitCallback, body, forceSemiFullScreen) {
    if (H5P.exitFullScreen !== undefined) {
        return; // Cannot enter new fullscreen until previous is over
    }

    if (H5P.isFramed && H5P.externalEmbed === false) {
        // Trigger resize on wrapper in parent window.
        window.parent.H5P.fullScreen($element, instance, exitCallback, H5P.$body.get(), forceSemiFullScreen);
        H5P.isFullscreen = true;
        H5P.exitFullScreen = function () {
            window.parent.H5P.exitFullScreen();
        };
        H5P.on(instance, 'exitFullScreen', function () {
            H5P.isFullscreen = false;
            H5P.exitFullScreen = undefined;
        });
        return;
    }

    var $container = $element;
    var $classes, $iframe, $body;
    if (body === undefined)  {
        $body = H5P.$body;
    }
    else {
        // We're called from an iframe.
        $body = H5P.jQuery(body);
        $classes = $body.add($element.get());
        var iframeSelector = '#h5p-iframe-' + $element.parent().data('content-id');
        $iframe = H5P.jQuery(iframeSelector);
        $element = $iframe.parent(); // Put iframe wrapper in fullscreen, not container.
    }

    $classes = $element.add(H5P.$body).add($classes);

    /**
     * Prepare for resize by setting the correct styles.
     *
     * @private
     * @param {string} classes CSS
     */
    var before = function (classes) {
        $classes.addClass(classes);

        if ($iframe !== undefined) {
            // Set iframe to its default size(100%).
            $iframe.css('height', '');
        }
    };

    /**
     * Gets called when fullscreen mode has been entered.
     * Resizes and sets focus on content.
     *
     * @private
     */
    var entered = function () {
        // Do not rely on window resize events.
        H5P.trigger(instance, 'resize');
        H5P.trigger(instance, 'focus');
        H5P.trigger(instance, 'enterFullScreen');
    };

    /**
     * Gets called when fullscreen mode has been exited.
     * Resizes and sets focus on content.
     *
     * @private
     * @param {string} classes CSS
     */
    var done = function (classes) {
        H5P.isFullscreen = false;
        $classes.removeClass(classes);

        // Do not rely on window resize events.
        H5P.trigger(instance, 'resize');
        H5P.trigger(instance, 'focus');

        H5P.exitFullScreen = undefined;
        if (exitCallback !== undefined) {
            exitCallback();
        }

        H5P.trigger(instance, 'exitFullScreen');
    };

    H5P.isFullscreen = true;
    if (H5P.fullScreenBrowserPrefix === undefined || forceSemiFullScreen === true) {
        // Create semi fullscreen.

        if (H5P.isFramed) {
            return; // TODO: Should we support semi-fullscreen for IE9 & 10 ?
        }

        before('h5p-semi-fullscreen');
        var $disable = H5P.jQuery('<div role="button" tabindex="0" class="h5p-disable-fullscreen" title="' + H5P.t('disableFullscreen') + '" aria-label="' + H5P.t('disableFullscreen') + '"></div>').appendTo($container.find('.h5p-content-controls'));
        var keyup, disableSemiFullscreen = H5P.exitFullScreen = function () {
            if (prevViewportContent) {
                // Use content from the previous viewport tag
                h5pViewport.content = prevViewportContent;
            }
            else {
                // Remove viewport tag
                head.removeChild(h5pViewport);
            }
            $disable.remove();
            $body.unbind('keyup', keyup);
            done('h5p-semi-fullscreen');
        };
        keyup = function (event) {
            if (event.keyCode === 27) {
                disableSemiFullscreen();
            }
        };
        $disable.click(disableSemiFullscreen);
        $body.keyup(keyup);

        // Disable zoom
        var prevViewportContent, h5pViewport;
        var metaTags = document.getElementsByTagName('meta');
        for (var i = 0; i < metaTags.length; i++) {
            if (metaTags[i].name === 'viewport') {
                // Use the existing viewport tag
                h5pViewport = metaTags[i];
                prevViewportContent = h5pViewport.content;
                break;
            }
        }
        if (!prevViewportContent) {
            // Create a new viewport tag
            h5pViewport = document.createElement('meta');
            h5pViewport.name = 'viewport';
        }
        h5pViewport.content = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0';
        if (!prevViewportContent) {
            // Insert the new viewport tag
            var head = document.getElementsByTagName('head')[0];
            head.appendChild(h5pViewport);
        }

        entered();
    }
    else {
        // Create real fullscreen.
        before('h5p-fullscreen');
        var first = true;

        document.addEventListener('fullscreenchange', function fullscreenCallback() {
            if (first) {
                // We are entering fullscreen mode
                first = false;
                setTimeout(entered, 200);
                return;
            }

            // We are exiting fullscreen
            setTimeout(() => done('h5p-fullscreen'), 200);
            document.removeEventListener('fullscreenchange', fullscreenCallback, false);
        });

        $element[0].requestFullscreen();

        H5P.exitFullScreen = () => {
            document.exitFullscreen();
        };
    }
};

