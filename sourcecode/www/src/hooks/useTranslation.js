import { useTranslation } from 'react-i18next';
import i18n from '../i18n';

export default (ns) => {
    return useTranslation(ns, {
        i18n,
    });
};
