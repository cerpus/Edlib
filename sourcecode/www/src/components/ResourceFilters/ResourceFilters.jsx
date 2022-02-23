import React from 'react';
import _ from 'lodash';
import { H5PTypes, Licenses } from './Filters';
import useTranslation from '../../hooks/useTranslation';
import { useEdlibComponentsContext } from '../../contexts/EdlibComponents';
import resourceFilters from '../../constants/resourceFilters';
import {
    Button,
    Collapse,
    List,
    ListItem,
    ListItemText,
    makeStyles,
} from '@material-ui/core';
import { ExpandLess, ExpandMore } from '@material-ui/icons';
import useArray from '../../hooks/useArray.js';
import SavedFilters from './Filters/SavedFilters.jsx';

const useStyles = makeStyles((theme) => ({
    root: {
        width: '100%',
        maxWidth: 360,
        backgroundColor: theme.palette.background.paper,
    },
    nested: {
        paddingLeft: theme.spacing(1),
    },
    mainCategories: {
        fontSize: '1.1em',
    },
}));

const ResourceFilters = ({
    filters,
    filterCount,
    contentTypeData,
    licenseData,
    savedFilterData,
    updateSavedFilter,
}) => {
    const { t } = useTranslation();
    const { getUserConfig } = useEdlibComponentsContext();
    const classes = useStyles();
    const disabledFilters = getUserConfig('disabledFilters') || null;

    const filterBlocks = [
        {
            type: resourceFilters.SAVED_FILTERS,
            title: _.capitalize(t('saved_filter', { count: 2 })),
            content: (
                <SavedFilters
                    savedFilterData={savedFilterData}
                    licenseData={licenseData}
                    contentTypeData={contentTypeData}
                    filters={filters}
                    updateSavedFilter={updateSavedFilter}
                />
            ),
        },
        {
            type: resourceFilters.H5P_TYPE,
            title: _.capitalize(t('content_type', { count: 2 })),
            count: filters.contentTypes.value.length,
            content: (
                <H5PTypes
                    contentTypeData={contentTypeData}
                    contentTypes={filters.contentTypes}
                    filterCount={filterCount ? filterCount.contentTypes : []}
                />
            ),
        },
        {
            type: resourceFilters.LICENSE,
            title: _.capitalize(t('license', { count: 1 })),
            count: filters.licenses.value.length,
            content: (
                <Licenses
                    licenseData={licenseData}
                    licenses={filters.licenses}
                    filterCount={filterCount ? filterCount.licenses : []}
                />
            ),
        },
    ];
    const open = useArray([resourceFilters.H5P_TYPE, resourceFilters.LICENSE]);

    return (
        <>
            <List component="nav" className={classes.root} dense>
                {filterBlocks
                    .filter(
                        (filterBlock) =>
                            disabledFilters === null ||
                            disabledFilters.indexOf(filterBlock.type) === -1
                    )
                    .map((filterBlock) => (
                        <React.Fragment key={filterBlock.type}>
                            <ListItem
                                button
                                onClick={() => open.toggle(filterBlock.type)}
                            >
                                <ListItemText
                                    classes={{
                                        primary: classes.mainCategories,
                                    }}
                                >
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
                        </React.Fragment>
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
                {_.capitalize(t('reset'))}
            </Button>
        </>
    );
};

export default ResourceFilters;
