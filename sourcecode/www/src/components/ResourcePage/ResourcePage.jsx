import React from 'react';
import PropTypes from 'prop-types';
import cn from 'classnames';
import styled from 'styled-components';
import { Input, useIsDevice } from '@cerpus/ui';
import { Tune as TuneIcon } from '@mui/icons-material';
import { Spinner } from '@cerpus/ui';
import _ from 'lodash';
import useTranslation from '../../hooks/useTranslation';
import ResourceFilters from '../ResourceFilters';
import ResourceTable from '../ResourceTable';
import useGetResources from '../../hooks/requests/useGetResources';
import {
    Button,
    FormControl,
    Icon,
    InputAdornment,
    InputLabel,
    MenuItem,
    Select,
    TablePagination,
    TextField,
    Box,
    IconButton,
} from '@mui/material';
import {
    Search as SearchIcon,
    List as ListIcon,
    ViewModule as ViewModuleIcon,
} from '@mui/icons-material';
import resourceOrders from '../../constants/resourceOrders';
import { useLocation } from 'react-router-dom';
import queryString from 'query-string';
import LanguageDropdown from '../LanguageDropdown';
import FilterChips from './components/FilterChips.jsx';
import FilterUtils from '../ResourceFilters/Filters/filterUtils.js';
import CardView from './components/CardView.jsx';

const StyledResourcePage = styled.div`
    background-color: #f3f3f3;
    position: relative;
    display: flex;
    height: 100%;
    max-height: 100%;
    flex-basis: 0;

    & > div {
        flex: 1 1 auto;
        max-height: 100%;
        overflow-y: auto;
        align-self: stretch;
    }

    & > div:first-child {
        flex-shrink: 0;
        max-width: 300px;
        width: 300px;
        padding: 5px;
    }

    .pageContent {
        overflow-y: scroll;
        display: flex;
        flex-direction: column;
        padding: 15px;

        & > .content {
            flex: 1;
        }
    }

    .contentOptions {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        margin-top: 10px;
    }

    .layoutOptions {
        display: flex;
        cursor: pointer;

        > div:not(.selected) {
            color: #b8b8b8;
        }
    }

    .mobileSearch {
        width: 100%;
    }

    .mobileSearchButtons {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
    }
`;

const MobileBackground = styled.div`
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1;
    cursor: pointer;
`;

const Filters = styled.div`
    background-color: white;
    box-shadow: 5px 0 5px 0 rgba(0, 0, 0, 0.16);
    overflow-y: auto;

    &.filtersMobile {
        position: absolute;
        right: 110%;
        z-index: 2;

        &.expanded {
            width: 100vw;
            right: unset;
            left: 0;
        }
    }
`;

const PaginationWrapper = styled.div`
    margin-top: 40px;
    padding-bottom: 10px;
    display: flex;
    justify-content: center;
`;

const Content = styled.div`
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
`;

const getOrderText = (t, order) => {
    switch (order) {
        case resourceOrders.UPDATED_AT_DESC:
            return _.capitalize(t('last_changed'));
        case resourceOrders.UPDATED_AT_ASC:
            return _.capitalize(t('first_changed'));
        case resourceOrders.VIEWS_DESC:
            return _.capitalize(t('most_used'));
        case resourceOrders.VIEWS_ASC:
            return _.capitalize(t('least_used'));
        case resourceOrders.RELEVANT_DESC:
            return _.capitalize(t('most_relevant'));
        default:
            return '';
    }
};

const useDefaultOrder = () => {
    const location = useLocation();

    return React.useMemo(() => {
        const query = queryString.parse(location.search);

        if (
            !query.sortBy ||
            Object.values(resourceOrders).indexOf(query.sortBy) === -1
        ) {
            return resourceOrders.UPDATED_AT_DESC;
        }

        return query.sortBy;
    }, []);
};

const ResourcePage = ({ filters, showDeleteButton = false }) => {
    const { t } = useTranslation();

    const [filtersExpanded, setFiltersExpanded] = React.useState(false);
    const [sortingOrder, setSortingOrder] = React.useState(useDefaultOrder());
    const filterMobileView = useIsDevice('<', 'md');
    const forceGridView = useIsDevice('<=', 'md');
    const [page, setPage] = React.useState(0);
    const [pageSize, setPageSize] = React.useState(40);
    const [_isGridView, setIsGridView] = React.useState(false);
    const filterUtils = FilterUtils(filters);
    const isGridView = forceGridView || _isGridView;

    const { error, loading, resources, pagination, refetch, filterCount } =
        useGetResources(
            React.useMemo(
                () => ({
                    limit: pageSize,
                    offset: page * pageSize,
                    resourceCapabilities: ['view'],
                    orderBy: sortingOrder,
                    ...(filters && filters.requestData),
                }),
                [page, sortingOrder, filters && filters.requestData, pageSize]
            )
        );

    React.useEffect(() => {
        setPage(0);
    }, [sortingOrder, filters.requestData]);

    const setSearch = React.useCallback(
        (searchText) => {
            filters.setSearchInput(searchText);
            if (sortingOrder !== resourceOrders.RELEVANT_DESC) {
                setSortingOrder(resourceOrders.RELEVANT_DESC);
            }
        },
        [filters, sortingOrder, setSortingOrder]
    );

    const sortOrderDropDown = (
        <FormControl variant="outlined">
            <InputLabel>{t('Sortering')}</InputLabel>
            <Select
                MenuProps={{
                    style: { zIndex: 2051 },
                    anchorOrigin: {
                        vertical: 'bottom',
                        horizontal: 'center',
                    },
                    transformOrigin: {
                        vertical: 'top',
                        horizontal: 'center',
                    },
                }}
                value={sortingOrder}
                onChange={(e) => setSortingOrder(e.target.value)}
                label={getOrderText(t, sortingOrder)}
            >
                {[
                    resourceOrders.RELEVANT_DESC,
                    resourceOrders.VIEWS_DESC,
                    resourceOrders.VIEWS_ASC,
                    resourceOrders.UPDATED_AT_DESC,
                    resourceOrders.UPDATED_AT_ASC,
                ].map((value, index) => (
                    <MenuItem key={index} value={value}>
                        {getOrderText(t, value)}
                    </MenuItem>
                ))}
            </Select>
        </FormControl>
    );

    return (
        <StyledResourcePage>
            <Filters
                className={cn({
                    filtersMobile: filterMobileView,
                    expanded: filtersExpanded,
                })}
            >
                <ResourceFilters filters={filters} filterCount={filterCount} />
            </Filters>
            {filterMobileView && filtersExpanded && (
                <MobileBackground onClick={() => setFiltersExpanded(false)} />
            )}
            <div className="pageContent">
                <div className="contentOptions">
                    <Box display="flex" paddingRight={1}>
                        <Box
                            paddingRight={1}
                            style={{
                                width: 400,
                            }}
                        >
                            <TextField
                                fullWidth
                                label={t('Søk')}
                                variant="outlined"
                                value={filters.searchInput}
                                onChange={(e) => setSearch(e.target.value)}
                                InputProps={{
                                    endAdornment: (
                                        <InputAdornment position="end">
                                            <Icon>
                                                <SearchIcon />
                                            </Icon>
                                        </InputAdornment>
                                    ),
                                }}
                            />
                        </Box>
                        <div
                            style={{
                                width: 200,
                            }}
                        >
                            <LanguageDropdown
                                language={
                                    filters.languages.length !== 0
                                        ? filters.languages[0]
                                        : null
                                }
                                setLanguage={(value) =>
                                    filters.languages.setValue(
                                        value ? [value] : []
                                    )
                                }
                            />
                        </div>
                    </Box>
                    <div>{sortOrderDropDown}</div>
                </div>
                {filterMobileView && (
                    <Box pt={1}>
                        <Button
                            color="primary"
                            variant="contained"
                            onClick={() => setFiltersExpanded(!filtersExpanded)}
                            startIcon={<TuneIcon />}
                        >
                            {t('filter', { count: 2 })}
                        </Button>
                    </Box>
                )}
                <Box
                    display="flex"
                    flexDirection="row"
                    justifyContent="space-between"
                    pt={1}
                >
                    <Box>
                        <FilterChips
                            chips={filterUtils.getChipsFromFilters()}
                        />
                    </Box>
                    <Box>
                        {!forceGridView && (
                            <IconButton
                                onClick={() => setIsGridView(!isGridView)}
                                size="large"
                            >
                                {isGridView ? <ListIcon /> : <ViewModuleIcon />}
                            </IconButton>
                        )}
                    </Box>
                </Box>
                <Content>
                    <div style={{ marginTop: 20 }}>
                        {loading && <Spinner />}
                        {error && <div>{t('Noe skjedde')}</div>}
                        {!loading && !error && resources && !isGridView && (
                            <ResourceTable
                                totalCount={pagination.totalCount}
                                resources={resources}
                                refetch={refetch}
                                showDeleteButton={showDeleteButton}
                                sortingOrder={sortingOrder}
                                setSortingOrder={setSortingOrder}
                            />
                        )}
                        {!loading && !error && resources && isGridView && (
                            <CardView
                                totalCount={pagination.totalCount}
                                resources={resources}
                                refetch={refetch}
                                showDeleteButton={showDeleteButton}
                            />
                        )}
                    </div>
                    {pagination && (
                        <PaginationWrapper>
                            <TablePagination
                                component="div"
                                count={pagination.totalCount}
                                page={page}
                                onPageChange={(e, page) => {
                                    setPage(page);
                                }}
                                rowsPerPage={pageSize}
                                onRowsPerPageChange={(e, pageSize) => {
                                    setPageSize(pageSize);
                                    setPage(0);
                                }}
                                rowsPerPageOptions={[40]}
                            />
                        </PaginationWrapper>
                    )}
                </Content>
            </div>
        </StyledResourcePage>
    );
};

ResourcePage.propTypes = {
    filters: PropTypes.object.isRequired,
    selectedResource: PropTypes.object,
};

export default ResourcePage;
