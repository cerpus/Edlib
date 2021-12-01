(function ($) {
    $(document).ready(function () {
        const Integration = typeof H5PIntegration !== "undefined" ? H5PIntegration : ArticleIntegration;
        if (Integration === undefined) {
            return;
        }
        if (typeof CKEDITOR !== 'undefined') {
            if (typeof Integration.editor.wirisPath !== 'undefined') {
                // Add wiris plugin
                CKEDITOR.plugins.addExternal('ckeditor_wiris', Integration.editor.wirisPath);
            }

            if (Integration.hasOwnProperty('editor') && Integration.editor.hasOwnProperty('extraAllowedContent')) {
                if( CKEDITOR.hasOwnProperty('extraAllowedContent') !== true){
                    CKEDITOR.config.extraAllowedContent = "";
                }
                CKEDITOR.config.extraAllowedContent += Integration.editor.extraAllowedContent;
            }

            if( Integration.hasOwnProperty('editor') && Integration.editor.hasOwnProperty('editorBodyClass') ){
                CKEDITOR.config.bodyClass = Integration.editor.editorBodyClass;
            }
        }

        if (window.parent !== null && typeof H5PEditor !== "undefined") {
            window.parent.IframeH5PEditor = H5PEditor;
        }

        if (Integration.hasOwnProperty('crossorigin') && Integration.crossorigin === true &&
            Integration.hasOwnProperty('crossoriginRegex') && Integration.crossoriginRegex !== null) {
            Integration.crossoriginRegex = new RegExp(Integration.crossoriginRegex);
        }
    });
})(typeof H5P !== "undefined" ? H5P.jQuery : $);
