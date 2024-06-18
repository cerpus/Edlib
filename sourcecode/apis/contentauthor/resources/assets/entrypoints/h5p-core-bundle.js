import jQuery from 'jquery';

window.H5P = window.H5P || {};
window.H5P.jQuery = jQuery;

// do not replace these with imports
require('../../../vendor/h5p/h5p-core/js/h5p.js');
require('../../../vendor/h5p/h5p-core/js/h5p-event-dispatcher.js');
require('../../../vendor/h5p/h5p-core/js/h5p-x-api-event.js');
require('../../../vendor/h5p/h5p-core/js/h5p-x-api.js');
require('../../../public/js/h5p/core-override/h5p-content-type.js'); //TODO Replaced to support patch-version in library folder name. Used by libraries to loads assets that is not js or css
require('../../../vendor/h5p/h5p-core/js/h5p-confirmation-dialog.js');
require('../../../vendor/h5p/h5p-core/js/h5p-action-bar.js');
require('../../../public/js/h5p/core-override/request-queue.js'); //TODO Change to vanilla H5P when they fix https://github.com/h5p/h5p-php-library/pull/66
require('../../../vendor/h5p/h5p-core/js/h5p-tooltip.js');
