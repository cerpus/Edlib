'use strict';

let byHelp = '<p>Others who use your work in any way must give you credit the way you request, but not in a way that suggests you endorse them or their use. If they want to use your work without giving you credit or for endorsement purposes, they must get your permission first.</p>';
let saHelp = '<p>You let others copy, distribute, display, perform, and modify your work, as long as they distribute any modified work on the same terms. If they want to distribute modified works under other terms, they must get your permission first.</p>';
let ndHelp = '<p>You let others copy, distribute, display and perform only original copies of your work. If they want to modify your work, they must get your permission first.</p>';
let ncHelp = '<p>You let others copy, distribute, display, perform, modify and use your work for any purpose other than commercially unless they get your permission first.</p>';
let noLicenseHelp = '<p>No license is set.</p>';

module.exports = {
    locale: 'nn-NO',
    messages: {
        'LICENSE.PRIVATE': 'Copyright',
        'LICENSE.PRIVATE.HELP': 'All rights reserved',
        'LICENSE.COPYRIGHT': 'Copyright',
        'LICENSE.COPYRIGHT.HELP': 'All rights reserved',
        'LICENSE.CC0': 'Creative Commons Zero',
        'LICENSE.CC0.HELP': 'Use the Creative Commons Zero license to waive your rights to this work.',
        'LICENSE.BY': 'CC Attribution',
        'LICENSE.BY.HELP': byHelp,
        'LICENSE.BY-SA': 'CC Attribution-ShareAlike',
        'LICENSE.BY-SA.HELP': byHelp + saHelp,
        'LICENSE.BY-ND': 'CC Attribution-NoDerivatives',
        'LICENSE.BY-ND.HELP': byHelp + ndHelp,
        'LICENSE.BY-NC': 'CC Attribution-NonCommercial',
        'LICENSE.BY-NC.HELP': byHelp + ncHelp,
        'LICENSE.BY-NC-SA': 'CC Attribution-NonCommercial-ShareAlike',
        'LICENSE.BY-NC-SA.HELP': byHelp + ncHelp + saHelp,
        'LICENSE.BY-NC-ND': 'CC Attribution-NonCommercial-NoDerivatives',
        'LICENSE.BY-NC-ND.HELP': byHelp + ncHelp + ndHelp,
        'LICENSE..HELP': noLicenseHelp,
        'LICENSE.PDM': 'Public Domain Mark',
        'LICENSE.PDM.HELP': 'Use the Public Domain Mark to mark works that are no longer restricted by copyright.',
        'LICENSE.EDLL': 'Edlib License',
        'LICENSE.EDLL.HELP': 'You grant to EdLib (the Company) a worldwide, non-exclusive, royalty-free, transferable licence (with right to sublicense) to use, reproduce, distribute, prepare derivative works of, display, and perform that Submitted Content in connection with the provision of the Service and otherwise in connection with the provision of the Service and The Company’s business, including without limitation for promoting and redistributing part or all of the Service (and derivative works thereof) in any media formats and through any media channels.',
    }
};
