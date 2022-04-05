var H5PPresave = H5PPresave || {};

/**
 * Resolve the presave logic for the content type ImagePair
 *
 * @param {object} content
 * @param finished
 * @constructor
 */
H5PPresave['H5P.ImagePair'] = function (content, finished) {
  var presave = H5PEditor.Presave;

  if (isContentInvalid()) {
    throw new presave.exceptions.InvalidContentSemanticsException('Invalid ImagePair Error');
  }

  var score = content.cards.length;

  presave.validateScore(score);

  finished({maxScore: score});

  /**
   * Check if required parameters is present
   * @return {boolean}
   */
  function isContentInvalid() {
    return !presave.checkNestedRequirements(content, 'content.cards') || !Array.isArray(content.cards);
  }
};
