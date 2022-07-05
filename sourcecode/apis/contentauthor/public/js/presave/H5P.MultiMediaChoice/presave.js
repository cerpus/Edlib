var H5PPresave = H5PPresave || {};

/**
 * Resolve the presave logic for the content type Multi Media Choice
 *
 * @param {object} content
 * @param finished
 * @constructor
 */
H5PPresave['H5P.MultiMediaChoice'] = function (content, finished) {
    const presave = H5PEditor.Presave;
    let score = 0;

    if (isContentInvalid()) {
        throw new presave.exceptions.InvalidContentSemanticsException('Invalid Multi Media Choice Error');
    }

    if (isSinglePoint()) {
        score = 1;
    } else {
        const correctAnswers = content.options.filter(function (answer) {
            return answer.correct;
        });
        score = Math.max(correctAnswers.length, 1);
    }

    presave.validateScore(score);
    finished({maxScore: score});

    /**
     * Check if required parameters is present
     * @return {boolean}
     */
    function isContentInvalid() {
        return !presave.checkNestedRequirements(content, 'content.options') || !Array.isArray(content.options);
    }

    /**
     * Check if content gives one point for all
     * @return {boolean}
     */
    function isSinglePoint() {
        return (presave.checkNestedRequirements(content, 'content.behaviour.singlePoint') && content.behaviour.singlePoint === true) ||
            (presave.checkNestedRequirements(content, 'content.behaviour.questionType') && content.behaviour.questionType === 'single');
    }
};
