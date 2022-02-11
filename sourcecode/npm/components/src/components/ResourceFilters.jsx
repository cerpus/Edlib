import React from 'react';
import { H5PTypes, Licenses } from './Filters';
import useTranslation from '../hooks/useTranslation';
import { useEdlibComponentsContext } from '../contexts/EdlibComponents';
import resourceFilters from '../constants/resourceFilters';
import {
    Button,
    Collapse,
    List,
    ListItem,
    ListItemText,
    makeStyles,
} from '@material-ui/core';
import { ExpandLess, ExpandMore } from '@material-ui/icons';
import useArray from '../hooks/useArray.js';

const useStyles = makeStyles((theme) => ({
    root: {
        width: '100%',
        maxWidth: 360,
        backgroundColor: theme.palette.background.paper,
    },
    nested: {
        paddingLeft: theme.spacing(1),
    },
}));

const ResourceFilters = ({ filters }) => {
    const { t } = useTranslation();
    const { getUserConfig } = useEdlibComponentsContext();
    const classes = useStyles();
    const disabledFilters = getUserConfig('disabledFilters') || null;

    const filterBlocks = [
        {
            type: resourceFilters.H5P_TYPE,
            title: t('Innholdstype'),
            count: filters.contentTypes.value.length,
            content: <H5PTypes contentTypes={filters.contentTypes} />,
        },
        {
            type: resourceFilters.LICENSE,
            title: t('Lisens'),
            count: filters.licenses.value.length,
            content: <Licenses licenses={filters.licenses} />,
        },
    ];
    const open = useArray();

    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                e.stopPropagation();
            }}
        >
            <List component="nav" className={classes.root} dense>
                {filterBlocks
                    .filter(
                        (filterBlock) =>
                            disabledFilters === null ||
                            disabledFilters.indexOf(filterBlock.type) === -1
                    )
                    .map((filterBlock) => (
                        <>
                            <ListItem
                                button
                                onClick={() => open.toggle(filterBlock.type)}
                            >
                                <ListItemText>
                                    <strong>{t(filterBlock.title)}</strong>
                                </ListItemText>
                                {open.has(filterBlock.type) ? (
                                    <ExpandLess />
                                ) : (
                                    <ExpandMore />
                                )}
                            </ListItem>
                            <Collapse
                                in={open.has(filterBlock.type)}
                                timeout="auto"
                                unmountOnExit
                            >
                                {filterBlock.content}
                            </Collapse>
                        </>
                    ))}
            </List>
            <Button
                variant="outlined"
                color="primary"
                fullWidth
                type="gray"
                onClick={() => {
                    filters.reset();
                }}
            >
                {t('Reset')}
            </Button>
        </form>
    );
};

export default ResourceFilters;
