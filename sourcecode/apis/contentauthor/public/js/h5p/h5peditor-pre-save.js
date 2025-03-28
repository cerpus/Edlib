var H5PEditor = H5PEditor || {};
var H5PPresave = H5PPresave || {};
var H5PPresaveCache = {};

H5PEditor.Presave = (function (Editor) {
  "use strict";

  /**
   * Presave structure
   *
   * @class
   */

  function Presave() {
    this.maxScore = 0;
  }

  /**
   * Process the given library and calculate the max score
   *
   * @public
   * @param {string} library
   * @param {object} content
   * @returns {H5PEditor.Presave}
   */
  Presave.prototype.process = function (library, content) {
    var self = this;

    if (Presave.libraryExists(library) === true) {
      const machineName = Presave.sanitizeLibrary(library);
      H5PPresave[machineName](content, function (serverSideData) {
        if (typeof serverSideData !== 'object') {
          return;
        }
        if (serverSideData.hasOwnProperty('maxScore') && Presave.isInt(serverSideData.maxScore)) {
          self.maxScore += serverSideData.maxScore;
        }
      });
    }
    return this;
  };

  /**
   * Check if the score is valid or throw exception if not
   *
   * @static
   * @param score
   * @returns {boolean}
   * @throws {Presave.exceptions.InvalidMaxScoreException} If score is not valid
   */
  Presave.validateScore = function (score) {
    if (!Presave.isInt(score) || score < 0) {
      throw new this.exceptions.InvalidMaxScoreException();
    }
    return true;
  };

  /**
   * Check if a object has the given properties.
   *
   * @static
   * @param {object} content
   * @param {string|[]} requirements
   * @returns {boolean}
   */
  Presave.checkNestedRequirements = function (content, requirements) {
    if (typeof content === 'undefined') {
      return false;
    }
    if (typeof requirements === 'string') {
      requirements = requirements.split('.');
    }
    for (var i = 1; i < requirements.length; i++) {
      if (!content.hasOwnProperty(requirements[i])) {
        return false;
      }
      content = content[requirements[i]];
    }
    return true;
  };

  /**
   * Check if value is a integer
   *
   * @static
   * @param {*} value
   * @returns {boolean}
   */
  Presave.isInt = function (value) {
    return !isNaN(value) && (function (x) {
      return (x | 0) === x;
    })(parseFloat(value));
  };

  /**
   * Checks if given library exists as a presave function
   *
   * @static
   * @param {string} library
   * @returns {boolean}
   */
  Presave.libraryExists = function (library) {
      const parts = Editor.libraryFromString(library);

      if (!parts) {
          return false;
      }

      const { machineName } = parts;

      if (!H5PPresave.hasOwnProperty(machineName)) {
          if (H5PPresaveCache.hasOwnProperty(library)) {
              if (H5PPresaveCache[library] !== false) {
                window?.eval(H5PPresaveCache[library]);
              }
          } else {
              const folderName = library.replace(' ', '-');
              $.ajax({
                  method: 'GET',
                  url: `${H5PLibraryPath}/${folderName}/presave.js`,
                  dataType: "script",
                  async: false,
                  cache: true,
              })
                  .done(script => {
                      H5PPresaveCache[library] = script;
                  })
                  .fail(jqXHR => {
                      H5PPresaveCache[library] = false;
                      if (![403, 404].includes(jqXHR.status)) {
                          console.error(jqXHR);
                          throw new Error(`Error loading script for ${library}`);
                      }
                  });
          }
      }

      return typeof H5PPresave[machineName] === 'function';
  };

  /**
   * Remove potential version number from library
   *
   * @param {string} library
   * @returns {*}
   */
  Presave.sanitizeLibrary = function (library) {
    return Editor.libraryFromString(library).machineName || library;
  };

  /**
   * Collection of common exceptions related to the logic handled in this file
   *
   * @type {{InvalidMaxScoreException: H5PEditor.Presave.exceptions.InvalidMaxScoreException, InvalidContentSemanticsException: H5PEditor.Presave.exceptions.InvalidContentSemanticsException}}
   */
  Presave.exceptions = {
    InvalidMaxScoreException: function (message) {
      this.message = typeof message === 'string' ? message : Editor.t('core', 'errorCalculatingMaxScore');
      this.name = 'InvalidMaxScoreError';
      this.code = 'H5P-P400';
    },
    InvalidContentSemanticsException: function (name, message) {
      this.message = typeof message === 'string' ? message : Editor.t('core', 'semanticsError', {':error': Editor.t('core', 'maxScoreSemanticsMissing')});
      this.name = typeof name === 'string' ? name : 'Invalid Content Semantics Error';
      this.code = 'H5P-P500';
    }
  };

  /**
   * C
   * @constructor
   * @type {Presave}
   */
  Presave.prototype.constructor = Presave;
  return Presave;
})(H5PEditor);
