var H5PPresave = H5PPresave || {};

/**
 * Resolve the presave logic for the content type Sort Paragraphs
 *
 * @param {object} content
 * @param finished
 * @constructor
 */
H5PPresave['H5P.SortParagraphs'] = function (content, finished) {
  var presave = H5PEditor.Presave;

  if (isContentInvalid()) {
    throw new presave.exceptions.InvalidContentSemanticsException('Invalid Sort Paragraphs Error');
  }

  var mode = content.behaviour.scoringMode;
  var score = content.paragraphs.length;

  if (mode === 'transitions') {
      score = score - 1;
  }

  presave.validateScore(score);

  finished({maxScore: score});

  /**
   * Check if required parameters is present
   * @return {boolean}
   */
  function isContentInvalid() {
    return !presave.checkNestedRequirements(content, 'content.paragraphs') ||
        !Array.isArray(content.paragraphs) ||
        !presave.checkNestedRequirements(content, 'content.behaviour.scoringMode');
  }
};
