import { memo } from 'react';
import useTranslation from '../../hooks/useTranslation';

export default memo(({ value }) => {
    const { t } = useTranslation();

    const units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB'];
    let idx = 0;
    let number = value;

    while (number > 1024 && units[idx+1]) {
        number = number / 1024;
        idx ++;
    }
    return t('filesize_format', { value: number, postfix: units[idx] });
});
