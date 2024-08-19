/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function (config) {
    config.toolbar = [
        {name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Subscript' ,'Superscript']},
        {name: 'insert', items: ['Edlibmatheditor']},
    ];

    config.removeButtons = '';

    config.extraPlugins = 'edlibmatheditor';

    // Disable version check, https://ckeditor.com/docs/ckeditor4/latest/api/CKEDITOR_config.html#cfg-versionCheck
    config.versionCheck = false;
};
