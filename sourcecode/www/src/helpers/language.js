// import { iso6393 } from 'iso-639-3';

export const iso6393ToString = (code) => {
    return code;
    const info = iso6393.find((languageInfo) => languageInfo.iso6393 === code);

    if (info) {
        return info.name;
    }

    return code;
};
