import React from 'react';
import _ from 'lodash';
import {
    Button,
    Dialog,
    DialogActions,
    DialogTitle,
    DialogContent,
    FormControl,
    InputLabel,
    Select,
    MenuItem,
    Box,
} from '@mui/material';
import { makeStyles } from 'tss-react/mui';
import useTranslation from '../../../../hooks/useTranslation.js';
import FilterChips from '../../../ResourcePage/components/FilterChips.jsx';
import useRequestWithToken from '../../../../hooks/useRequestWithToken.jsx';
import { useConfigurationContext } from '../../../../contexts/Configuration.jsx';

const useStyles = makeStyles()((theme) => ({
    formControl: {
        marginBottom: theme.spacing(2),
    },
}));

const DeleteSavedFilter = ({
    show,
    onClose,
    savedFilterData,
    onDeleted,
    filterUtils,
}) => {
    const { classes } = useStyles();
    const { t } = useTranslation();
    const request = useRequestWithToken();
    const { edlibApi } = useConfigurationContext();

    const [selected, setSelected] = React.useState(null);
    const selectedSavedFilter = React.useMemo(() => {
        if (!selected) {
            return null;
        }

        return savedFilterData.find((sfd) => sfd.id === selected);
    }, [selected]);

    return (
        <Dialog open={show} onClose={() => onClose()} maxWidth="sm" fullWidth>
            <DialogTitle>{_.capitalize(t('delete_filter'))}</DialogTitle>
            <DialogContent>
                <Box pt={1}>
                    <FormControl
                        variant="outlined"
                        fullWidth
                        className={classes.formControl}
                    >
                        <InputLabel>
                            {_.capitalize(t('choose_group'))}
                        </InputLabel>
                        <Select
                            value={selected}
                            onChange={(e) => setSelected(e.target.value)}
                            label={_.capitalize(t('choose_group'))}
                        >
                            {savedFilterData.map((savedFilter) => (
                                <MenuItem
                                    key={savedFilter.id}
                                    value={savedFilter.id}
                                >
                                    {savedFilter.name}
                                </MenuItem>
                            ))}
                        </Select>
                    </FormControl>
                    <div className={classes.formControl}>
                        <FilterChips
                            chips={filterUtils.getChipsFromChoices(
                                selectedSavedFilter
                                    ? selectedSavedFilter.choices
                                    : []
                            )}
                            color="default"
                        />
                    </div>
                </Box>
            </DialogContent>
            <DialogActions>
                <Button
                    color="primary"
                    variant="outlined"
                    onClick={() => onClose()}
                >
                    {t('cancel')}
                </Button>
                <Button
                    color="primary"
                    variant="contained"
                    style={{ marginLeft: 5 }}
                    disabled={!selected}
                    onClick={() => {
                        if (!selected) {
                            return;
                        }

                        let url = edlibApi(`/common/saved-filters/${selected}`);

                        request(url, 'DELETE', {
                            json: false,
                        }).then(() => onDeleted(selected));
                    }}
                >
                    {t('delete')}
                </Button>
            </DialogActions>
        </Dialog>
    );
};

export default DeleteSavedFilter;
