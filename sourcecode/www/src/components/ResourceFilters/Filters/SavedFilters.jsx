import React from 'react';
import {
    Box,
    Button,
    Checkbox,
    List,
    ListItem,
    ListItemIcon,
    ListItemText,
} from '@mui/material';
import { makeStyles } from 'tss-react/mui';
import useTranslation from '../../../hooks/useTranslation.js';
import CreateSavedFilter from './components/CreateSavedFilter.jsx';
import FilterUtils from './filterUtils.js';
import DeleteSavedFilter from './components/DeleteSavedFilter.jsx';

const useStyles = makeStyles()((theme) => ({
    nested: {
        paddingLeft: theme.spacing(1),
    },
    checkboxRoot: {
        height: 20,
        boxSizing: 'border-box',
    },
    listItemIcon: {
        minWidth: 30,
    },
}));

const SavedFilters = ({
    savedFilterData,
    filters,
    updateSavedFilter,
    licenseData,
    contentTypeData,
    showCreateFilter,
    setShowCreateFilter,
}) => {
    const { t } = useTranslation();
    const { classes } = useStyles();
    const [showDelete, setShowDelete] = React.useState(false);
    const filterUtils = FilterUtils(filters, {
        contentTypes: contentTypeData,
        licenses: licenseData,
    });

    return (
        <>
            <List
                dense
                component="div"
                disablePadding
                className={classes.nested}
            >
                {savedFilterData.map((savedFilter) => {
                    return (
                        <ListItem
                            key={savedFilter.id}
                            button
                            dense
                            onClick={() =>
                                filterUtils.setFilterFromChoices(
                                    savedFilter.choices
                                )
                            }
                        >
                            <ListItemIcon
                                classes={{
                                    root: classes.listItemIcon,
                                }}
                            >
                                <Checkbox
                                    size="small"
                                    edge="start"
                                    checked={filterUtils.areFiltersAndChoicesIdentical(
                                        savedFilter.choices
                                    )}
                                    tabIndex={-1}
                                    disableRipple
                                    color="primary"
                                    classes={{
                                        root: classes.checkboxRoot,
                                    }}
                                />
                            </ListItemIcon>
                            <ListItemText primary={savedFilter.name} />
                        </ListItem>
                    );
                })}
                <ListItem dense>
                    <Box>
                        <Button
                            color="primary"
                            variant="outlined"
                            onClick={() => setShowDelete(true)}
                            size="small"
                            disabled={savedFilterData.length === 0}
                        >
                            {t('delete')}
                        </Button>
                    </Box>
                </ListItem>
            </List>
            <CreateSavedFilter
                show={showCreateFilter}
                onClose={() => setShowCreateFilter(false)}
                savedFilterData={savedFilterData}
                filters={filters}
                onDone={(savedFilter) => {
                    setShowCreateFilter(false);
                    updateSavedFilter(savedFilter);
                }}
                filterUtils={filterUtils}
            />
            <DeleteSavedFilter
                show={showDelete}
                onClose={() => setShowDelete(false)}
                savedFilterData={savedFilterData}
                filters={filters}
                onDeleted={(id) => {
                    setShowDelete(false);
                    updateSavedFilter(id, true);
                }}
                filterUtils={filterUtils}
            />
        </>
    );
};

export default SavedFilters;
