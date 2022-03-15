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
    TextField,
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

const CreateSavedFilter = ({
    show,
    onClose,
    savedFilterData,
    filters,
    onDone,
    filterUtils,
}) => {
    const { classes } = useStyles();
    const { t } = useTranslation();
    const { edlibApi } = useConfigurationContext();
    const [selected, setSelected] = React.useState('new');
    const [newFilterName, setNewFilterName] = React.useState('My filter');

    const request = useRequestWithToken();

    return (
        <Dialog open={show} onClose={() => onClose()} maxWidth="sm" fullWidth>
            <DialogTitle>{_.capitalize(t('edit_filter'))}</DialogTitle>
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
                            <MenuItem value="new">
                                <em>{_.capitalize(t('create_new'))}</em>
                            </MenuItem>
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
                    {selected === 'new' && (
                        <TextField
                            required
                            label={_.capitalize('name')}
                            variant="outlined"
                            className={classes.formControl}
                            fullWidth
                            value={newFilterName}
                            onChange={(e) => setNewFilterName(e.target.value)}
                        />
                    )}
                    <div className={classes.formControl}>
                        <FilterChips
                            chips={filterUtils.getChipsFromFilters(false)}
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
                    onClick={() => {
                        let url = edlibApi(`/common/saved-filters`);

                        if (selected !== 'new') {
                            url += `/` + selected;
                        }

                        request(url, 'POST', {
                            body: {
                                name: newFilterName,
                                choices: [
                                    filters.contentTypes.value.map(
                                        (contentType) => ({
                                            group: 'contentType',
                                            value: contentType.value,
                                        })
                                    ),
                                    filters.licenses.value.map(
                                        (contentType) => ({
                                            group: 'license',
                                            value: contentType.value,
                                        })
                                    ),
                                ].flat(),
                            },
                        }).then((data) => onDone(data));
                    }}
                >
                    {t('save')}
                </Button>
            </DialogActions>
        </Dialog>
    );
};

export default CreateSavedFilter;
