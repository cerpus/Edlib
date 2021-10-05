export default h5peditor => {
    if (h5peditor == null) {
        return null;
    }

    const params = h5peditor.getParams();
    if (!params || !params.params) {
        return null;
    }

    if (!h5peditor.isMainTitleSet()) {
        return null;
    }

    return {
        title: params.metadata.title || '',
        library: h5peditor.getLibrary(),
        parameters: params,
        maxscore: h5peditor.getMaxScore(params.params),
    };
};
