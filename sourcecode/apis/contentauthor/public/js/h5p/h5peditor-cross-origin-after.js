/**
 * Load after 'vendor/h5p/h5p-editor/scripts/h5peditor.js'.
 *
 * Restores window.parent replaced by 'h5peditor-cross-origin-before.js'.
 *
 * Logging is only enabled when window.h5pEditorCrossOriginDebug is true (development environment).
 */
(function () {
    const log = function (level) {
        if (window.h5pEditorCrossOriginDebug === true) {
            console[level].apply(console, ['[h5peditor-cross-origin]'].concat(Array.prototype.slice.call(arguments, 1)));
        }
    };

    if (!window.H5PEditor || typeof window.H5PEditor.resetLoadedLibraries !== 'function') {
        log('error', 'H5PEditor was not initialised by h5peditor.js', window.H5PEditor);
    }
    if (window.__h5pEditorRealParent) {
        try {
            window.parent = window.__h5pEditorRealParent;
            delete window.__h5pEditorRealParent;
        } catch (e) {
            log('error', 'Failed to restore window.parent', e);
        }
        if (window.parent === window) {
            log('error', 'window.parent was not restored, messages to the parent window will not work');
        }
    }
})();
