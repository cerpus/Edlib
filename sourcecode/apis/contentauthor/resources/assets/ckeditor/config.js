/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function (config) {
    // Define changes to default configuration here.
    // For complete reference see:
    // http://docs.ckeditor.com/#!/api/CKEDITOR.config

    // The toolbar groups arrangement, optimized for two toolbar rows.
    config.toolbarGroups = [
        { name: 'clipboard', groups: ['clipboard', 'undo'] },
        { name: 'editing', groups: ['find', 'selection'] },
        { name: 'links' },
        { name: 'insert' },
        { name: 'forms' },
        { name: 'tools' },
        { name: 'document', groups: ['mode', 'document', 'doctools'] },
        { name: 'others' },
        '/',
        { name: 'basicstyles', groups: ['basicstyles', 'cleanup'] },
        { name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi'] },
        { name: 'styles' },
        { name: 'colors' },
        { name: 'about' }
    ];

    // Remove some buttons provided by the standard plugins, which are
    // not needed in the Standard(s) toolbar.
    config.removeButtons = 'Underline,Subscript,Superscript';

    // Set the most common block elements.
    config.format_tags = 'p;h1;h2;h3;pre';

    // Simplify the dialog windows.
    config.removeDialogTabs = 'image:advanced;link:advanced';

    config.extraPlugins = 'widget,dialog,iframedialog,autogrow,uploadimage';

    config.autoGrow_bottomSpace = 50;
    config.autoGrow_onStartup = true;

    // This is the maximum allowed width of a edlib widget
    config.edlib_maxWidth = 840;

    // There is a 20px margin on the body element of the editor
    config.width = '100%';
    config.resize_maxWidth = config.edlib_maxWidth + (20 * 2);

    config.stylesSet = 'EdlibStyles:/js/ckeditor/EdlibStyles.js';

    // config.extraAllowedContent = 'section aside header h1 h2 h3 h4 h5 h6 p ul ol li br b strong iframe embed *(*)[class,data-*];' +
    // 'math maction maligngroup malignmark menclose merror mfenced mfrac mglyph mi mlabeledtr mlongdiv mmultiscripts mn mo mover mpadded mphantom mroot mrow ms mscarries ' +
    // 'mscarry msgroup msline mspace msqrt msrow mstack mstyle msub msup msubsup mtable mtd mtext mtr munder munderover semantics annotation annotation-xml';
};
