(function ($, ns) {
    ns.init = function() {
        H5PEditor.$ = H5P.jQuery;
        H5PEditor.basePath = H5PIntegration.editor.libraryUrl;
        H5PEditor.fileIcon = H5PIntegration.editor.fileIcon;
        H5PEditor.ajaxPath = H5PIntegration.editor.ajaxPath;
        H5PEditor.filesPath = H5PIntegration.editor.filesPath;
        H5PEditor.apiVersion = H5PIntegration.editor.apiVersion;
        H5PEditor.contentLanguage = H5PIntegration.editor.language;

        // Semantics describing what copyright information can be stored for media.
        H5PEditor.copyrightSemantics = H5PIntegration.editor.copyrightSemantics;
        H5PEditor.metadataSemantics = H5PIntegration.editor.metadataSemantics;

        // Required styles and scripts for the editor
        H5PEditor.assets = H5PIntegration.editor.assets;

        // Required for assets
        H5PEditor.baseUrl = '';

        if (H5PIntegration.editor.nodeVersionId !== undefined) {
            H5PEditor.contentId = H5PIntegration.editor.nodeVersionId;
        }

        var h5peditor;
        var $form = $('#content-form');
        var $type = $('input[name="action"]');
        var $upload = $('.h5p-upload');
        var $create = $('.h5p-create').hide();
        var $editor = $('.h5p-editor');
        var $library = $('input[name="library"]');
        var $params = $('input[name="parameters"]');
        var $maxscore = $('input[name="max_score"]');
        var library = H5PIntegration.editor.contentType || $library.val();
        var $title = $('#content-form #title');

        $type.change(function () {
            if ($type.filter(':checked').val() === 'upload') {
                $create.hide();
                $upload.show();
            }
            else {
                $upload.hide();
                if (h5peditor === undefined) {
                    h5peditor = new ns.Editor(library, $params.val(), $editor[0]);
                }
                $create.show();
            }
        });

        if ($type.filter(':checked').val() === 'upload') {
            $type.change();
        }
        else {
            $type.filter('input[value="create"]').attr('checked', true).change();
        }

        $form.submit(function (event) {
            if (event.isDefaultPrevented() === true) {
                return false;
            }
            event.preventDefault();
            if (typeof storeContent !== "function") {
                alert("Missing vital function 'storeContent'");
                return;
            }
            if (h5peditor !== undefined) {
                var params = h5peditor.getParams();
                if (params === false || params === null || params.params === undefined || params.params === false) {
                    return false;
                }

                if (!h5peditor.isMainTitleSet()) {
                    return event.preventDefault();
                }

                // Set the title field to the metadata title if the field exists
                if ($title && $title.length !== 0) {
                    $title.val(params.metadata.title || '');
                }

                // Set main library
                $library.val(h5peditor.getLibrary());

                // Set params
                $params.val(JSON.stringify(params));

                $maxscore.val(h5peditor.getMaxScore(params.params));

                const $errorBox = $("#content-form-error-box");
                $errorBox.hide();
                storeContent(this.getAttribute('action'), this, function (response) {
                    let responseData;
                    try {
                        responseData = JSON.parse(response.responseText);
                    } catch (err) {
                        responseData = [response.responseText];
                    }
                    $errorBox.show();
                    const $errorList = $errorBox.find('ul');
                    $errorList.html("");
                    $.each(responseData, function (key, value) {
                        $errorList.append($("<li>").html(value));
                    });
                });
            }
        });

        var $label = $title.prev();
        $title.focus(function () {
            $label.addClass('screen-reader-text');
        }).blur(function () {
            if ($title.val() === '') {
                $label.removeClass('screen-reader-text');
            }
        }).focus();

        // Delete confirm
        $('.submitdelete').click(function () {
            return confirm(H5PIntegration.editor.deleteMessage);
        });

        H5PEditor.stageUpgrade = function (library, params) {
            $editor.empty();
            $create.html($editor);
            h5peditor = new H5PEditor.Editor(library, params, $editor);
            $library.val(library);
        };

        H5PEditor.beforeUpgrade = function () {
            var params = h5peditor.getParams();
            if (params !== undefined && params !== false) {
                return {
                    params: JSON.stringify(params),
                    library: h5peditor.getLibrary(),
                };
            }
            return null;
        }

    };

    H5PEditor.getAjaxUrl = function (action, parameters) {
        var url = H5PIntegration.editor.ajaxPath + action;

        if (parameters !== undefined) {
            var separator = url.indexOf('?') === -1 ? '?' : '&';
            for (var property in parameters) {
                if (parameters.hasOwnProperty(property)) {
                    url += separator + property + '=' + parameters[property];
                    separator = '&';
                }
            }
        }

        return url;
    };

    $(window).ready(ns.init);
})(H5P.jQuery, H5PEditor);
