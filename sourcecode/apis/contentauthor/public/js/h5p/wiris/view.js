(function ($) {
    function isReady() {
        return (
            window.com &&
            window.com.wiris &&
            window.com.wiris.js &&
            window.com.wiris.js.JsPluginViewer
        );
    }

    function loadedAndReady() {
        // Find H5P content
        $(".h5p-content").each(function (i, e) {
            var doWiris = function (node) {
                if (
                    node &&
                    !node.getAttribute("data-wiris-processed") &&
                    node.parentNode
                ) {
                    node.setAttribute("data-wiris-processed", true);

                    // Use WIRIS to process the math element
                    if (window.com.wiris.js.JsPluginViewer.parseElement) {
                        window.com.wiris.js.JsPluginViewer.parseElement(
                            node.parentNode
                        );
                    } else if (window.com.wiris.js.JsPluginViewer.parse) {
                        window.com.wiris.js.JsPluginViewer.parse(
                            node.parentNode
                        );
                    }
                }
            };

            var MutationObserver =
                window.MutationObserver || window.WebKitMutationObserver;

            if (!MutationObserver) {
                // Fallback for older browsers
                var check = function () {
                    $("math", e).each(function (j, m) {
                        doWiris(m);
                    });
                    checkInterval = setTimeout(check, 2000);
                };
                var checkInterval = setTimeout(check, 2000);
            } else {
                var running = false;
                var limitedResize = function () {
                    if (!running) {
                        running = setTimeout(function () {
                            $("math", e).each(function (j, m) {
                                doWiris(m);
                            });
                            running = null;
                        }, 500); // 2 fps cap
                    }
                };

                var observer = new MutationObserver(function (mutations) {
                    for (var i = 0; i < mutations.length; i++) {
                        if (mutations[i].addedNodes.length) {
                            // Check if any added nodes contain math elements
                            mutations[i].addedNodes.forEach(function (node) {
                                if (node.nodeType === 1) {
                                    // Element node
                                    // Look for math elements in the new content
                                    var mathElements = $(node)
                                        .find("math")
                                        .addBack("math");
                                    mathElements.each(function (j, m) {
                                        doWiris(m);
                                    });
                                }
                            });
                            limitedResize();
                        }
                    }
                });
                observer.observe(e, {
                    childList: true,
                    subtree: true,
                });
            }
        });
    }

    $(document).ready(function () {
        var attempts = 0;
        var loaderInterval = setInterval(function () {
            if (isReady()) {
                clearInterval(loaderInterval);
                loadedAndReady();
            }
            if (attempts >= 20) {
                clearInterval(loaderInterval);
                console.warn(
                    "WIRIS JsPluginViewer not found after 20 attempts"
                );
            }
            attempts++;
        }, 50);
    });
})(H5P.jQuery);
