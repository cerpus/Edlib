'use strict';

let byHelp = 'Others who use your work in any way must give you credit the way you request, but not in a way that suggests you endorse them or their use. If they want to use your work without giving you credit or for endorsement purposes, they must get your permission first.';
let saHelp = 'You let others copy, distribute, display, perform, and modify your work, as long as they distribute any modified work on the same terms. If they want to distribute modified works under other terms, they must get your permission first.';
let ndHelp = 'You let others copy, distribute, display and perform only original copies of your work. If they want to modify your work, they must get your permission first.';
let ncHelp = 'You let others copy, distribute, display, perform, modify and use your work for any purpose other than commercially unless they get your permission first.';

module.exports = {
    locale: 'nn-NO',
    messages: {
        'LICENSECHOOSER.YES': 'Yes',
        'LICENSECHOOSER.NO': 'No',

        'LICENSECHOOSER.ADAPTIONS': 'ALLOW ADAPTIONS OF YOUR CONTENT TO BE SHARED?',
        'LICENSECHOOSER.OPTION-SHAREALIKE': 'Yes, as long as others share alike.',

        'LICENSECHOOSER.COMMERCIAL-USE': 'ALLOW YOUR CONTENT TO BE USED FOR COMMERCIAL WORK?',

        'LICENSECHOOSER.ATTRIBUTION-TITLE': 'ATTRIBUTION DETAILS',
        'LICENSECHOOSER.ATTRIBUTION-FIELD-TITLE': 'Title of work',
        'LICENSECHOOSER.ATTRIBUTION-FIELD-NAME': 'Attribute work to name',
        'LICENSECHOOSER.ATTRIBUTION-FIELD-URL': 'Attribute work to URL',
        'LICENSECHOOSER.ATTRIBUTION-FIELD-TITLE-PLACEHOLDER': 'Work title',
        'LICENSECHOOSER.ATTRIBUTION-FIELD-NAME-PLACEHOLDER': 'Your name',
        'LICENSECHOOSER.ATTRIBUTION-FIELD-URL-PLACEHOLDER': 'Enter URL',

        'LICENSECHOOSER.RESTRICTION-LEVEL': 'LEVEL OF RESERVATION',
        'LICENSECHOOSER.PUBLIC-DOMAIN': 'No rights reserved',
        'LICENSECHOOSER.CREATIVE-COMMONS': 'Some rights reserved',
        'LICENSECHOOSER.COPYRIGHT': 'All rights reserved',

        'LICENSECHOOSER.ATTRIBUTION-HELP': 'This allows users of your work to determine how to contact you or where to go for more information about the work.',

        'LICENSECHOOSER.ADAPTIONS-HELP': '<p><strong>Yes</strong><br>' +
            'The licensor permits others to copy, distribute, display, and perform the work, as well as make and distribute derivative works based on it.</p>' +
            '<p><strong>Yes, as long as others share alike</strong><br>' + saHelp + '</p>' +
            '<p><strong>No</strong><br>' + ndHelp + '</p>',

        'LICENSECHOOSER.COMMERCIAL-USE-HELP': '<p><strong>Yes</strong><br>' +
            'The licensor permits others to copy, distribute, display, and perform the work, including for commercial purposes.</p>' +
            '<p><strong>No</strong><br>' + ncHelp + '</p>',

        'LICENSECHOOSER.RESTRICTION-LEVEL-HELP': '<p><strong>No rights reserved</strong><br>Select this license if you are a holder of copyright or database rights and you wish to waive all your interests, if any, in your work worldwide. This may be the case if you are reproducing an underlying work that is in the public domain and want to communicate that you claim no copyright in your digital copy where copyright law may grant protection.</p>' +
            '<p><strong>Some rights reserved</strong><br>Use this if you want to use a Creative Commons license.<br>' + byHelp + '</p>' +
            '<p><strong>Edlib License</strong><br>You decide over the content. But the content can be used by Edlib for, but not limited to, marketing purposes</p>',

        'LICENSECHOOSER.PUBLICDOMAIN': 'Choose a Public Domain license',
        'LICENSECHOOSER.PUBLICDOMAIN.HELP': '<p><strong>Creative Commons Zero</strong><br>' +
            'Select this license if you are the owner of this work and want to waive your copyrights.</p>' +
            '<p><strong>Public Domain Mark</strong><br>' +
            'Select this license if this work is no longer restricted by copyright.</p>',

        'LICENSECHOOSER.PUBLICDOMAIN.CC0': 'Creative Commons Zero',
        'LICENSECHOOSER.PUBLICDOMAIN.PDM': 'Public Domain Mark',
        'LICENSECHOOSER.EDLL': 'Edlib License',

    }
};
