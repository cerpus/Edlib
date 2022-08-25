import React from 'react';
import {
    Box,
    Button,
    Checkbox,
    List,
    ListItemButton,
    ListItemIcon,
    ListItemText,
} from '@mui/material';
import { makeStyles } from 'tss-react/mui';
import useTranslation from '../../../hooks/useTranslation.js';

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

const SavedFilters = ({ savedFilterData, setShowDelete, filterUtils }) => {
    const { t } = useTranslation();
    const { classes } = useStyles();

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
                        <ListItemButton
                            key={savedFilter.id}
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
                        </ListItemButton>
                    );
                })}
                <ListItemButton dense>
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
                </ListItemButton>
            </List>
        </>
    );
};

export default SavedFilters;
