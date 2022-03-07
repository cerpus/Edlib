import React from 'react';
import _ from 'lodash';
import store from 'store';
import { H5PTypes, Licenses } from './Filters';
import useTranslation from '../../hooks/useTranslation';
import { useEdlibComponentsContext } from '../../contexts/EdlibComponents';
import resourceFilters from '../../constants/resourceFilters';
import {
    Box,
    Button,
    Collapse,
    List,
    ListItem,
    ListItemText,
} from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';
import {
    ExpandLess,
    ExpandMore,
    FilterList as FilterListIcon,
} from '@mui/icons-material';
import useArray from '../../hooks/useArray.js';
import SavedFilters from './Filters/SavedFilters.jsx';
import viewTypes from './filterViewTypes';

const useStyles = makeStyles((theme) => ({
    root: {
        width: '100%',
        maxWidth: 360,
        backgroundColor: theme.palette.background.paper,
        padding: 0,
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
    const [filterViewType, _setFilterViewType] = React.useState(() => {
        return store.get('filterViewType', viewTypes.GROUPED);
    });
    const setFilterViewType = React.useCallback(
        (value) => {
            _setFilterViewType(value);
            store.set('filterViewType', value);
        },
        [_setFilterViewType]
    );

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
                    filterViewType={filterViewType}
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
            <Box pr={1} display="flex" justifyContent="end">
                <Button
                    onClick={() =>
                        setFilterViewType(
                            filterViewType === viewTypes.GROUPED
                                ? viewTypes.ALPHABETICAL
                                : viewTypes.GROUPED
                        )
                    }
                    endIcon={<FilterListIcon />}
                    style={{
                        color: 'inherit',
                    }}
                    size="small"
                >
                    {filterViewType === viewTypes.GROUPED
                        ? t('grouped')
                        : t('A-Z')}
                </Button>
            </Box>
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
