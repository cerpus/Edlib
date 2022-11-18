import React from 'react';
import useTranslation from '../../../hooks/useTranslation.js';
import {
    Checkbox,
    List,
    ListItemButton,
    ListItemIcon,
    ListItemText,
} from '@mui/material';
import { makeStyles } from 'tss-react/mui';

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

const Licenses = ({ licenses, filterCount, licenseData }) => {
    const { t } = useTranslation();
    const { classes } = useStyles();

    return (
        <List dense component="div" disablePadding className={classes.nested}>
            {licenseData
                .map((item) => {
                    const parts = item.id.split('-');
                    const count = filterCount.find(
                        (filterCount) =>
                            filterCount.key === item.id.toLowerCase()
                    );

                    return {
                        title: parts
                            .map((part) => t(`licenses.${part}`))
                            .join(' - '),
                        value: item.id,
                        filteredCount: count ? count.count : 0,
                    };
                })
                .sort((a, b) =>
                    a.title < b.title ? -1 : a.title > b.title ? 1 : 0
                )
                .map((license) => (
                    <ListItemButton
                        key={license.value}
                        dense
                        onClick={() => licenses.toggle(license)}
                        disabled={license.filteredCount === 0}
                    >
                        <ListItemIcon
                            classes={{
                                root: classes.listItemIcon,
                            }}
                        >
                            <Checkbox
                                size="small"
                                edge="start"
                                checked={licenses.has(license)}
                                tabIndex={-1}
                                disableRipple
                                color="primary"
                                classes={{
                                    root: classes.checkboxRoot,
                                }}
                            />
                        </ListItemIcon>
                        <ListItemText
                            primary={`${license.title} (${license.filteredCount})`}
                        />
                    </ListItemButton>
                ))}
        </List>
    );
};

export default Licenses;
