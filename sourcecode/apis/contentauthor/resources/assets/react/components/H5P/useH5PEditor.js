import * as React from 'react';
import { nextTick } from 'utils/utils';

export default (onParameterChange) => {
    const [h5pEditor, setH5pEditor] = React.useState(null);
    const [editorRef, setEditorRef] = React.useState(null);
    const [iframeLoading, setIframeLoading] = React.useState(false);

    const init = (ref, parameters) => {
        const H5PEditor = window.H5PEditor;
        const H5PIntegration = window.H5PIntegration;
        const H5P = window.H5P;
        const ns = window.H5PEditor;

        const $ = H5P.jQuery;
        const $upload = $('.h5p-upload');
        const $create = $('.h5p-create').hide();
        const $editor = $(ref);
        setEditorRef($editor);

        H5PEditor.$ = H5P.jQuery;
        H5PEditor.basePath = H5PIntegration.editor.libraryUrl;
        H5PEditor.fileIcon = H5PIntegration.editor.fileIcon;
        H5PEditor.ajaxPath = H5PIntegration.editor.ajaxPath;
        H5PEditor.filesPath = H5PIntegration.editor.filesPath;
        H5PEditor.apiVersion = H5PIntegration.editor.apiVersion;
        H5PEditor.contentLanguage = H5PIntegration.editor.language;
        H5PEditor.defaultLanguage = H5PIntegration.editor.defaultLanguage;

        // Semantics describing what copyright information can be stored for media.
        H5PEditor.copyrightSemantics = H5PIntegration.editor.copyrightSemantics;
        H5PEditor.metadataSemantics = H5PIntegration.editor.metadataSemantics;

        // Required styles and scripts for the editor
        H5PEditor.assets = H5PIntegration.editor.assets;

        // Required for assets
        H5PEditor.baseUrl = '';

        H5PEditor.getAjaxUrl = function (action, parameters) {
            let url = H5PIntegration.editor.ajaxPath + action;

            if (parameters !== undefined) {
                let separator = url.indexOf('?') === -1 ? '?' : '&';
                for (const property in parameters) {
                    if (parameters.hasOwnProperty(property)) {
                        url += separator + property + '=' + parameters[property];
                        separator = '&';
                    }
                }
            }

            return url;
        };

        if (H5PIntegration.editor.nodeVersionId !== undefined) {
            H5PEditor.contentId = H5PIntegration.editor.nodeVersionId;
        }

        nextTick(() => {
            $upload.hide();
            if (!h5pEditor) {
                setIframeLoading(true);
                setH5pEditor(new ns.Editor(
                    parameters.library,
                    JSON.stringify(parameters.parameters),
                    ref,
                    iframeLoaded
                ));
            }
            $create.show();
        });

        // Delete confirm
        $('.submitdelete').click(() => {
            return confirm(H5PIntegration.editor.deleteMessage);
        });
    };

    const getParams = () => h5pEditor && h5pEditor.getParams();
    const getLibrary = () => h5pEditor && h5pEditor.getLibrary();
    const iframeLoaded = () => setIframeLoading(false);
    const setAuthor = (name, role) => {
        if (h5pEditor) {
            h5pEditor.selector.form.metadataForm.setMetadata({authors: [{name, role}]});
        }
    };

    const onBeforeUpgrade = params => {
        if (!params !== undefined && params !== false) {
            return {
                params: JSON.stringify(params),
                library: getLibrary(),
            };
        }

        return null;
    };

    const stageUpgrade = (library, params) => {
        setIframeLoading(true);
        const $ = window.H5P.jQuery;
        const $create = $('.h5p-create');
        editorRef.empty();
        $create.html(editorRef);
        nextTick(() => {
            setH5pEditor(new H5PEditor.Editor(library, params, editorRef[0], () => {
                onParameterChange({
                    parameters: JSON.parse(params),
                    library,
                });
                iframeLoaded();
            }));
        });
    };

    const reDraw = (parameters, library) => {
        if (h5pEditor && !iframeLoading) {
            setIframeLoading(true);
            const $ = window.H5P.jQuery;
            const $create = $('.h5p-create');

            editorRef.empty();
            $create.html(editorRef);
            const H5PEditor = window.H5PEditor;
            setH5pEditor(new H5PEditor.Editor(
                library,
                JSON.stringify(parameters),
                editorRef[0],
                iframeLoaded
            ));
        }
    };

    return {
        init,
        reDraw,
        onBeforeUpgrade,
        getParams,
        getLibrary,
        stageUpgrade,
        setAuthor,
        getMaxScore: parameters => {
            const params = parameters || getParams();

            if (!params) {
                return null;
            }

            return h5pEditor.getMaxScore(params.params);
        },
        getTitle: () => {
            const params = getParams();

            if (!params) {
                return null;
            }

            return (params.metadata && params.metadata.title) || '';
        },
        h5pEditor,
        iframeLoading,
    };
};
