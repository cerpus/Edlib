import { useTranslation as _useTranslation } from 'react-i18next';
import i18n from '../i18n';

const useTranslation = (ns) => {
    return _useTranslation(ns, {
        i18n,
    });
};

export default useTranslation;
