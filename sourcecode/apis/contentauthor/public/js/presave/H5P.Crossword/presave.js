var H5PPresave = H5PPresave || {};

/**
 * Resolve the presave logic for the content type Crossword
 *
 * @param {object} content
 * @param finished
 * @constructor
 */
H5PPresave['H5P.Crossword'] = function (content, finished) {
    const presave = H5PEditor.Presave;

    if (isContentInvalid()) {
        throw new presave.exceptions.InvalidContentSemanticsException('Invalid Crossword Error');
    }
    // Two score types are used: Number of correct words or correct characters
    const scoreWords = content.behaviour.scoreWords;
    const words = content.words.length;
    const fixedWords = content.words.reduce((prev, curr) => (prev + (curr.fixWord ? 1 : 0)), 0);
    const poolSize = getPoolSize();
    const usesPool = (poolSize > 1 && poolSize < words);

    // Max score can only be calculated if score is number of correct words
    if (scoreWords) {
        const pool = fixedWords > poolSize ? fixedWords : poolSize;
        const score = usesPool ? pool : words;
        presave.validateScore(score);
        finished({maxScore: score});
    } else {
        finished({});
    }

    /**
     * Check if required parameters is present
     * @return {boolean}
     */
    function isContentInvalid() {
        return !presave.checkNestedRequirements(content, 'content.words') ||
            !Array.isArray(content.words) ||
            !presave.checkNestedRequirements(content, 'content.behaviour.scoreWords');
    }

    /**
     * Get word pool size, if set
     * @return number
     */
    function getPoolSize() {
        if (presave.checkNestedRequirements(content, 'content.behaviour.poolSize') &&
            typeof content.behaviour.poolSize === 'number'
        ) {
            return content.behaviour.poolSize;
        }

        return 0;
    }
};
