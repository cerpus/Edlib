import React from 'react';
import { Autocomplete } from '@mui/material';
import _ from 'lodash';
import useFetchWithToken from '../hooks/useFetchWithToken';
import { CircularProgress, TextField } from '@mui/material';
import { iso6393ToString } from '../helpers/language.js';
import useTranslation from '../hooks/useTranslation.js';
import { useConfigurationContext } from '../contexts/Configuration.jsx';

const LanguageDropdown = ({ language, setLanguage, filterCount }) => {
    const { t } = useTranslation();
    const { edlibApi } = useConfigurationContext();

    const [open, setOpen] = React.useState(false);
    const { error, loading, response } = useFetchWithToken(
        edlibApi('/resources/v1/languages'),
        'GET',
        React.useMemo(() => ({}), []),
        true,
        true
    );

    const getOptionCount = lng => filterCount.find(filterCount => filterCount.key === lng.toLowerCase())?.count ?? 0;
    const buildLanguageList = (data) => (
        data.map(lang => {
            const count = getOptionCount(lang);
            return {
                value: lang,
                disabled: count === 0,
                label: `${iso6393ToString(lang)} (${count})`,
            };
        })
        .sort((a, b) => (a.label < b.label ? -1 : a.label > b.label ? 1 : 0))
    );

    return (
        <Autocomplete
            fullWidth
            open={open}
            onOpen={() => {
                setOpen(true);
            }}
            onClose={() => {
                setOpen(false);
            }}
            getOptionDisabled={option => option.disabled}
            isOptionEqualToValue={(option, value) => option.value === value.value}
            options={buildLanguageList(response ? response.data : [])}
            loading={loading}
            onChange={(e, v) => {
                setLanguage(v?.value ?? null);
            }}
            value={language}
            renderInput={(params) => (
                <TextField
                    {...params}
                    fullWidth
                    label={_.capitalize(t('language'))}
                    variant="outlined"
                    InputProps={{
                        ...params.InputProps,
                        endAdornment: (
                            <>
                                {loading ? (
                                    <CircularProgress
                                        color="inherit"
                                        size={20}
                                    />
                                ) : null}
                                {params.InputProps.endAdornment}
                            </>
                        ),
                    }}
                />
            )}
        />
    );
};

export default LanguageDropdown;
