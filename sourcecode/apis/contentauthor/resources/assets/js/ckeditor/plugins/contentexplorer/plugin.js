'use strict';
/* global CKEDITOR*/
(function () {
    function ceInsertedContent(event) {
        removeListener();
        var ltiResource = event.data;
        var launchUrl = '/lti/launch?url=' + encodeURIComponent(ltiResource.url);
        var iframeTemplate = '<iframe class="contentexplorer" src=""></iframe>';
        var element = CKEDITOR.dom.element.createFromHtml(iframeTemplate);
        element.setAttribute('src', launchUrl);

        CKEDITOR.currentInstance.insertElement(element);
        CKEDITOR.dialog.getCurrent().hide();
    }

    function addListener() {
        window.addEventListener("message", ceInsertedContent, false);
    }

    function removeListener() {
        window.removeEventListener('message', ceInsertedContent)
    }

    CKEDITOR.plugins.add('contentexplorer', {
        requires: 'widget,dialog,iframedialog',
        icons: 'contentexplorer',
        init: function (editor) {
            CKEDITOR.dialog.addIframe(
                'contentexplorerDialog',
                'Content Explorer',
                '/lti/insert-resource',
                '100%',
                $(window).height() - 150,
                null,
                {
                    buttons: [],
                    resizable: CKEDITOR.DIALOG_RESIZE_NONE,
                    onCancel: function () {
                        removeListener()
                    },
                    onShow: function() {
                        addListener();
                    }
                }
            );

            editor.widgets.add('contentexplorer', {
                button: 'Content Explorer',
                template: '<iframe class="contentexplorer" src=""></iframe>',
                dialog: 'contentexplorerDialog',
                icon: this.path + 'icons/contentexplorer.png',
                allowedContent: {
                    'iframe': {
                        attributes: 'src',
                        classes: 'contentexplorer'
                    }
                },
                upcast: function (element) {
                    return (element.name === "iframe" && element.hasClass('contentexplorer'));
                }
            });
        }
    });
})();
