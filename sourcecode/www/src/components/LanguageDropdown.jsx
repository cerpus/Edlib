import React from 'react';
import { Autocomplete } from '@mui/material';
import _ from 'lodash';
import useFetchWithToken from '../hooks/useFetchWithToken';
import { CircularProgress, TextField } from '@mui/material';
import { iso6393ToString } from '../helpers/language.js';
import useTranslation from '../hooks/useTranslation.js';
import useConfig from '../hooks/useConfig.js';

const LanguageDropdown = ({ language, setLanguage }) => {
    const { t } = useTranslation();
    const { edlib } = useConfig();

    const [open, setOpen] = React.useState(false);
    const { error, loading, response } = useFetchWithToken(
        edlib('/resources/v1/languages'),
        'GET',
        React.useMemo(() => ({}), []),
        false,
        true,
        false
    );

    return (
        <Autocomplete
            open={open}
            onOpen={() => {
                setOpen(true);
            }}
            onClose={() => {
                setOpen(false);
            }}
            isOptionEqualToValue={(option, value) => option === value}
            getOptionLabel={(option) => iso6393ToString(option)}
            options={response ? response.data : []}
            loading={loading}
            onChange={(e, v) => {
                setLanguage(v);
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
