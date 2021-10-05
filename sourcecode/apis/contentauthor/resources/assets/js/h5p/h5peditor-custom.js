(function (H5P) {
    /**
     * Check if the given path has a protocol.
     *
     * @private
     * @param {string} path
     * @return {string}
     */
    function hasProtocol(path) {
        return path.match(/^[a-z0-9]+:\/\//i);
    }

    H5P.getCrossOrigin = overrideCrossOrigin(H5P.getCrossOrigin);

    function overrideCrossOrigin(original) {
        return source => {
            if (H5PIntegration.crossorigin && typeof source === 'object' && hasProtocol(source.path)) {
                return H5PIntegration.crossorigin && H5PIntegration.crossoriginRegex && source.path.match(H5PIntegration.crossoriginRegex) ? H5PIntegration.crossorigin : null;
            }
            return original(source);
        };
    }
})(H5P);