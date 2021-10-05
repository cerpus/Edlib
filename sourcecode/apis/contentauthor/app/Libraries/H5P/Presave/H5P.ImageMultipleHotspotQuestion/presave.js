var H5PEditor = H5PEditor || {};
var H5PPresave = H5PPresave || {};

/**
 * Resolve the presave logic for the content type Multiple Image Hotspots
 *
 * @param {object} content
 * @param finished
 * @constructor
 */
H5PPresave['H5P.ImageMultipleHotspotQuestion'] = function (content, finished) {
  var presave = H5PEditor.Presave;

  if (isContentInValid()) {
    throw {
      name: 'Invalid Find Multiple Hotspots Error',
      message: "Could not find expected semantics in content."
    };
  }

  var correctHotspots = content.imageMultipleHotspotQuestion.hotspotSettings.hotspot
    .filter(function (hotspot) {
      return hotspot.userSettings.correct;
    }).length;

  var score = useFixedNumberOfHotspots() ? content.imageMultipleHotspotQuestion.hotspotSettings.numberHotspots : correctHotspots;

  presave.validateScore(score);

  if (finished) {
    finished({maxScore: score});
  }

  /**
   * Check if required parameters is present
   * @return {boolean}
   */
  function isContentInValid() {
    return !presave.checkNestedRequirements(content, 'content.imageMultipleHotspotQuestion.hotspotSettings.hotspot') || !Array.isArray(content.imageMultipleHotspotQuestion.hotspotSettings.hotspot);
  }

  /**
   * Check if content uses fixed number of hotspots
   * @return {boolean}
   */
  function useFixedNumberOfHotspots() {
    return hasFixedNumberOfHotspots() && isFixedNumberOfHotspotsLessThanCorrectHotspots();
  }

  /**
   * Check if content has fixed number of hotspots
   * @return {boolean}
   */
  function hasFixedNumberOfHotspots(){
    return presave.checkNestedRequirements(content, 'content.imageMultipleHotspotQuestion.hotspotSettings.numberHotspots');
  }

  /**
   * Check if fixed number of hotspots is less than correct number of hotspots
   * @return {boolean}
   */
  function isFixedNumberOfHotspotsLessThanCorrectHotspots() {
    return content.imageMultipleHotspotQuestion.hotspotSettings.numberHotspots <= correctHotspots;
  }
};
