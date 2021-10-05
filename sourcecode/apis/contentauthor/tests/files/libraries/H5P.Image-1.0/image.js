var H5P = H5P || {};

/**
 * Constructor.
 *
 * @param {Object} params Options for this library.
 * @param {Number} id Content identifier
 * @returns {undefined}
 */
(function ($) {
  H5P.Image = function (params, id) {
    H5P.EventDispatcher.call(this);

    if (params.file === undefined || !(params.file instanceof Object)) {
      this.placeholder = true;
    }
    else {
      this.source = H5P.getPath(params.file.path, id);
      this.width = params.file.width;
      this.height = params.file.height;

      // Use new copyright information if available. Fallback to old.
      if (params.file.copyright !== undefined) {
        this.copyright = params.file.copyright;
      }
      else if (params.copyright !== undefined) {
        this.copyright = params.copyright;
      }
    }

    this.alt = params.alt !== undefined ? params.alt : 'New image';

    if (params.title !== undefined) {
      this.title = params.title;
    }
  };

  H5P.Image.prototype = Object.create(H5P.EventDispatcher.prototype);
  H5P.Image.prototype.constructor = H5P.Image;

  /**
   * Wipe out the content of the wrapper and put our HTML in it.
   *
   * @param {jQuery} $wrapper
   * @returns {undefined}
   */
  H5P.Image.prototype.attach = function ($wrapper) {
    var self = this;
    var source = this.source;

    if (self.$img === undefined) {
      if(self.placeholder) {
        self.$img = $('<div>', {
          width: '100%',
          height: '100%',
          class: 'h5p-placeholder',
          title: this.title === undefined ? '' : this.title,
          load: function () {
            self.trigger('loaded');
          }
        });
      } else {
        self.$img = $('<img>', {
          width: '100%',
          height: '100%',
          src: source,
          alt: this.alt,
          title: this.title === undefined ? '' : this.title,
          load: function () {
            self.trigger('loaded');
          }
        });
      }
    }

    $wrapper.addClass('h5p-image').html(self.$img);
  };

  /**
   * Gather copyright information for the current content.
   *
   * @returns {H5P.ContentCopyright}
   */
  H5P.Image.prototype.getCopyrights = function () {
    if (this.copyright === undefined) {
      return;
    }

    var info = new H5P.ContentCopyrights();

    var image = new H5P.MediaCopyright(this.copyright);
    image.setThumbnail(new H5P.Thumbnail(this.source, this.width, this.height));
    info.addMedia(image);

    return info;
  };

  return H5P.Image;
}(H5P.jQuery));
