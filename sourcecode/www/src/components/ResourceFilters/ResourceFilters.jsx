import React, { Fragment } from 'react';
import { capitalize } from 'lodash';
import { H5PTypes, Licenses } from './Filters';
import useTranslation from '../../hooks/useTranslation';
import { useEdlibComponentsContext } from '../../contexts/EdlibComponents';
import {
    H5P_TYPE,
    LICENSE,
    SAVED_FILTERS,
} from '../../constants/resourceFilters';
import {
    Box,
    Button,
    Collapse,
    List,
    ListItemButton,
    ListItemText,
} from '@mui/material';
import { makeStyles } from 'tss-react/mui';
import {
    ExpandLess,
    ExpandMore,
    FilterList as FilterListIcon,
} from '@mui/icons-material';
import SavedFilters from './Filters/SavedFilters.jsx';
import viewTypes from './filterViewTypes';
import { useConfigurationContext } from '../../contexts/Configuration.jsx';
import CreateSavedFilter from './Filters/components/CreateSavedFilter.jsx';
import FilterUtils from './Filters/filterUtils.js';
import DeleteSavedFilter from './Filters/components/DeleteSavedFilter.jsx';
import { useOpenFilters } from '../../contexts/FilterContext';

const useStyles = makeStyles()((theme) => ({
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

const FilterBlock = ({ children, disabled, onToggle, open, title }) => {
    const { classes } = useStyles();

    if (disabled) {
        return null;
    }

    return (
        <Fragment>
            <ListItemButton onClick={onToggle}>
                <ListItemText classes={{ primary: classes.mainCategories }}>
                    <strong>{title}</strong>
                </ListItemText>
                {open ? <ExpandLess /> : <ExpandMore />}
            </ListItemButton>
            <Collapse in={open} timeout="auto">
                {children}
            </Collapse>
        </Fragment>
    );
};

const ResourceFilters = ({
    filters,
    filterCount,
    contentTypeData,
    licenseData,
    savedFilterData,
    updateSavedFilter,
}) => {
    const { t } = useTranslation();
    const { classes } = useStyles();
    const { getUserConfig } = useEdlibComponentsContext();
    const { getConfigurationValue, setConfigurationValue } =
        useConfigurationContext();

    const disabledFilters = getUserConfig('disabledFilters') || [];
    const openFilters = useOpenFilters();
    const [filterViewType, _setFilterViewType] = React.useState(() => {
        return getConfigurationValue('filterViewType', viewTypes.GROUPED);
    });
    const setFilterViewType = React.useCallback(
        (value) => {
            _setFilterViewType(value);
            setConfigurationValue('filterViewType', value);
        },
        [_setFilterViewType]
    );

    const filterUtils = FilterUtils(filters, {
        contentTypes: contentTypeData,
        licenses: licenseData,
    });
    const [showCreateFilter, setShowCreateFilter] = React.useState(false);
    const [showDelete, setShowDelete] = React.useState(false);

    return (
        <>
            <Box px={1} pb={1} display="flex" justifyContent="space-between">
                <Button
                    color="primary"
                    variant="contained"
                    onClick={() => setShowCreateFilter(true)}
                    size="small"
                    disabled={
                        filters.contentTypes.value.length === 0 &&
                        filters.licenses.value.length === 0
                    }
                >
                    {t('save_filter')}
                </Button>
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
                <FilterBlock
                    disabled={disabledFilters.includes(SAVED_FILTERS)}
                    onToggle={() => openFilters.toggle(SAVED_FILTERS)}
                    title={capitalize(t('saved_filter', { count: 2 }))}
                >
                    <SavedFilters
                        savedFilterData={savedFilterData}
                        setShowDelete={setShowDelete}
                        filterUtils={filterUtils}
                    />
                </FilterBlock>
                <FilterBlock
                    disabled={disabledFilters.includes(H5P_TYPE)}
                    onToggle={() => openFilters.toggle(H5P_TYPE)}
                    open={openFilters.has(H5P_TYPE)}
                    title={capitalize(t('content_type', { count: 2 }))}
                >
                    <H5PTypes
                        contentTypeData={contentTypeData}
                        contentTypes={filters.contentTypes}
                        filterCount={filterCount ? filterCount.contentTypes : []}
                        filterViewType={filterViewType}
                    />
                </FilterBlock>
                <FilterBlock
                    disabled={disabledFilters.includes(LICENSE)}
                    onToggle={() => openFilters.toggle(LICENSE)}
                    open={openFilters.has(LICENSE)}
                    title={capitalize(t('license', { count: 1 }))}
                >
                    <Licenses
                        licenseData={licenseData}
                        licenses={filters.licenses}
                        filterCount={filterCount ? filterCount.licenses : []}
                    />
                </FilterBlock>
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
                {capitalize(t('reset'))}
            </Button>
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
                setShowDelete={setShowDelete}
            />
        </>
    );
};

export default ResourceFilters;
