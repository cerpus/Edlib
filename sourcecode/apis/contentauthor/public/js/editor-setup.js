(function ($) {
    $(document).ready(function () {
        const Integration = typeof H5PIntegration !== "undefined" ? H5PIntegration : ArticleIntegration;
        if (Integration === undefined) {
            return;
        }

        if (Integration.hasOwnProperty('crossorigin') && Integration.crossorigin === true &&
            Integration.hasOwnProperty('crossoriginRegex') && Integration.crossoriginRegex !== null) {
            Integration.crossoriginRegex = new RegExp(Integration.crossoriginRegex);
        }

        // Used by 'Update content' functionality, upgrading to a newer version of the content type, in Editor
        if (window.parent !== null && typeof H5PEditor !== "undefined") {
            window.parent.IframeH5PEditor = H5PEditor;
        }
    });
})(typeof H5P !== "undefined" ? H5P.jQuery : $);
