import languages from '../constants/languages.js';

export const iso6393ToString = (code) => {
    const info = languages.find(
        (languageInfo) => languageInfo.iso6393 === code
    );

    if (info) {
        return info.name;
    }

    return code;
};
