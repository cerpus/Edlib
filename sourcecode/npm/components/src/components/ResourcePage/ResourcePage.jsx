import React from 'react';
import PropTypes from 'prop-types';
import cn from 'classnames';
import styled from 'styled-components';
import { FormGroup, Input, useIsDevice } from '@cerpus/ui';
import { Tune as TuneIcon } from '@material-ui/icons';
import { Spinner } from '@cerpus/ui';
import ResourceModal from '../ResourceModal';
import ResourcePageFilterGroup from './components/ResourcePageFilterGroup';
import useTranslation from '../../hooks/useTranslation';
import ResourceFilters from '../ResourceFilters';
import ResourceTable from '../ResourceTable';
import useGetResources from '../../hooks/requests/useGetResources';
import {
    Button,
    FormControl,
    InputLabel,
    MenuItem,
    Select,
    TablePagination,
} from '@material-ui/core';
import resourceOrders from '../../constants/resourceOrders';
import { useLocation } from 'react-router-dom';
import queryString from 'query-string';

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
        max-width: 300px;
        padding: 20px;
    }

    .pageContent {
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

    .breadcrumbWrapper {
        padding-bottom: 10px;
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

const ResourceCount = styled.div`
    font-weight: bold;
    font-size: ${(props) => props.theme.rem(1.5)};
`;

const getOrderText = (t, order) => {
    switch (order) {
        case resourceOrders.RELEVANT:
            return t('Foreslåtte ressurser');
        case resourceOrders.CREATED:
            return t('Sist endret');
        case resourceOrders.USAGE:
            return t('Mest brukte');
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
            return resourceOrders.CREATED;
        }

        return query.sortBy;
    }, []);
};

const ResourcePage = ({
    breadcrumb,
    filters,
    selectedResource,
    setSelectedResource,
    showDeleteButton = false,
}) => {
    const { t } = useTranslation();
    const [filtersExpanded, setFiltersExpanded] = React.useState(false);
    const [sortingOrder, setSortingOrder] = React.useState(useDefaultOrder());
    const filterMobileView = useIsDevice('<', 'md');
    const [page, setPage] = React.useState(0);
    const [pageSize, setPageSize] = React.useState(40);

    const { error, loading, resources, pagination, refetch } = useGetResources(
        React.useMemo(
            () => ({
                limit: pageSize,
                skip: page * pageSize,
                resourceCapabilities: ['view'],
                sortingOrder,
                ...(filters && filters.requestData),
            }),
            [page, sortingOrder, filters && filters.requestData, pageSize]
        )
    );

    React.useEffect(() => {
        setPage(0);
    }, [sortingOrder, filters.requestData]);

    const sortOrderDropDown = (
        <FormControl variant="outlined">
            <InputLabel>{t('Sortering')}</InputLabel>
            <Select
                MenuProps={{
                    style: { zIndex: 2051 },
                }}
                value={sortingOrder}
                onChange={(e) => setSortingOrder(e.target.value)}
                label={getOrderText(t, sortingOrder)}
            >
                {['usage', 'created'].map((value, index) => (
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
                <ResourcePageFilterGroup title={t('Søk')}>
                    <FormGroup>
                        <Input
                            placeholder={t('Søk')}
                            value={filters.searchInput}
                            onChange={(e) =>
                                filters.setSearchInput(e.target.value)
                            }
                        />
                    </FormGroup>
                </ResourcePageFilterGroup>
                <ResourceFilters filters={filters} />
            </Filters>
            {filterMobileView && filtersExpanded && (
                <MobileBackground onClick={() => setFiltersExpanded(false)} />
            )}
            <div className="pageContent">
                {!filterMobileView && (
                    <div className="breadcrumbWrapper">{breadcrumb}</div>
                )}
                {filterMobileView && (
                    <div>
                        <Input
                            className="mobileSearch"
                            placeholder="Søk"
                            value={filters.searchInput}
                            onChange={(e) =>
                                filters.setSearchInput(e.target.value)
                            }
                        />
                        <div className="mobileSearchButtons">
                            <Button
                                color="primary"
                                variant="contained"
                                onClick={() =>
                                    setFiltersExpanded(!filtersExpanded)
                                }
                                startIcon={<TuneIcon />}
                            >
                                <span style={{ textTransform: 'uppercase' }}>
                                    {t('avansert søk')}
                                </span>
                            </Button>
                            {sortOrderDropDown}
                        </div>
                    </div>
                )}
                {!filterMobileView && (
                    <div className="contentOptions">
                        <div>
                            {pagination && (
                                <ResourceCount>{`${pagination.totalCount} ${t(
                                    'ressurser'
                                )}`}</ResourceCount>
                            )}
                        </div>
                        <div>{sortOrderDropDown}</div>
                    </div>
                )}
                <Content>
                    <div style={{ marginTop: 20 }}>
                        {loading && <Spinner />}
                        {error && <div>{t('Noe skjedde')}</div>}
                        {!loading && !error && resources && (
                            <ResourceTable
                                resources={resources}
                                onResourceClick={setSelectedResource}
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
                                onChangePage={(e, page) => {
                                    setPage(page);
                                }}
                                rowsPerPage={pageSize}
                                onChangeRowsPerPage={(e, pageSize) => {
                                    setPageSize(pageSize);
                                    setPage(0);
                                }}
                                rowsPerPageOptions={[40]}
                            />
                        </PaginationWrapper>
                    )}
                </Content>
            </div>
            <ResourceModal
                isOpen={!!selectedResource}
                onClose={() => setSelectedResource(null)}
                resource={selectedResource}
            />
        </StyledResourcePage>
    );
};

ResourcePage.propTypes = {
    breadcrumb: PropTypes.object.isRequired,
    filters: PropTypes.object.isRequired,
    selectedResource: PropTypes.object,
    setSelectedResource: PropTypes.func.isRequired,
};

export default ResourcePage;
