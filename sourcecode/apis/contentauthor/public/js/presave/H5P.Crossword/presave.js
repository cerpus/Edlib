var H5PPresave = H5PPresave || {};

/**
 * Resolve the presave logic for the content type Crossword
 *
 * @param {object} content
 * @param finished
 * @constructor
 */
H5PPresave['H5P.Crossword'] = function (content, finished) {
    var presave = H5PEditor.Presave;

    if (isContentInvalid()) {
        throw new presave.exceptions.InvalidContentSemanticsException('Invalid Crossword Error');
    }

    var score = content.words.length;

    presave.validateScore(score);

    finished({maxScore: score});

    /**
     * Check if required parameters is present
     * @return {boolean}
     */
    function isContentInvalid() {
        return !presave.checkNestedRequirements(content, 'content.words') || !Array.isArray(content.words);
    }
};
