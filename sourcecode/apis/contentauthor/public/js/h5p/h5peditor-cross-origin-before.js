/**
 * Load before 'vendor/h5p/h5p-editor/scripts/h5peditor.js'.
 *
 * h5peditor.js expects to run in an iframe with a same-origin parent and reads
 * window.parent.H5PEditor/H5PIntegration. When Content Author is embedded in
 * the Hub (different origin) this throws a DOMException and the editor is never
 * initialised. Temporarily point window.parent to the current window while
 * h5peditor.js runs. Restored by 'h5peditor-cross-origin-after.js'.
 *
 * Logging is only enabled when window.h5pEditorCrossOriginDebug is true (development environment).
 */
(function () {
    const log = function (level) {
        if (window.h5pEditorCrossOriginDebug === true) {
            console[level].apply(console, ['[h5peditor-cross-origin]'].concat(Array.prototype.slice.call(arguments, 1)));
        }
    };

    if (window.parent === window) {
        return;
    }
    try {
        // Throws if parent is cross-origin
        void window.parent.H5PEditor;
    } catch (e) {
        log('warn', 'Parent window is cross-origin, using current window as parent for h5peditor.js', e);
        try {
            window.__h5pEditorRealParent = window.parent;
            window.parent = window;
        } catch (err) {
            log('error', 'Failed to replace window.parent', err);
        }
        if (window.parent !== window) {
            log('error', 'window.parent could not be replaced, h5peditor.js will fail to initialise');
        }
    }
})();
