'use strict';

function ceInsertedContent(event) {
    if (typeof event.data.return_type === 'undefined' ||
        event.data.return_type !== 'lti_launch_url' ||
        typeof event.data.url === 'undefined') {
        return false;
    }
    removeListener();
    var ltiResource = event.data;
    var launchUrl = '/lti/launch?url=' + encodeURIComponent(ltiResource.url);
    var editor = CKEDITOR.currentInstance;
    var element = CKEDITOR.plugins.edlib.getNewElement();
    editor.insertElement(element);
    editor.widgets.initOn(
        element,
        'edlib',
        {
            src: launchUrl
        }
    );
    CKEDITOR.dialog.getCurrent().hide();
}

function addListener() {
    window.addEventListener("message", ceInsertedContent, false);
}

function removeListener() {
    window.removeEventListener('message', ceInsertedContent);
}

CKEDITOR.dialog.addIframe('edlibDialog',
    'edlib.com',
    '/lti/insert-resource',
    $(window).width() - 150,
    $(window).height() - 150,
    function () {
        // Empty function to prevent default function causing 'Uncaught SecurityError'
    },
    {
        buttons: [],
        resizable: CKEDITOR.DIALOG_RESIZE_NONE,
        onCancel: function () {
            removeListener()
        },
        onShow: function () {
            this.parts.dialog.addClass('edlib-dialog');
            addListener();
        }
    }
);
