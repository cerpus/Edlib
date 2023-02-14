/*This code has not been used anywhere in EDLIB.*/
import React from 'react';
import useTranslation from '../hooks/useTranslation';
import useGetResources from '../hooks/requests/useGetResources';
import Pagination from '../components/Pagination';
import { CircularProgress } from '@mui/material';
import styled from 'styled-components';

const pageSize = 40;

const Wrapper = styled.div`
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;

    height: 100%;
`;

const PaginationWrapper = styled.div`
    margin-top: 40px;
    display: flex;
    justify-content: center;
`;

const PaginatedResources = ({
    children,
    sortingOrder = 'relevant',
    filters = null,
}) => {
    const { t } = useTranslation();
    const [page, setPage] = React.useState(0);

    const { error, loading, resources, pagination } = useGetResources(
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
    }, [sortingOrder]);

    return (
        <Wrapper>
            <div>
                {loading && <CircularProgress />}
                {error && <div>{t('Noe skjedde')}</div>}
                {!loading &&
                    !error &&
                    resources &&
                    children({ resources, totalCount: pagination.totalCount })}
            </div>
            {pagination && (
                <PaginationWrapper>
                    <Pagination
                        currentPage={page}
                        pageCount={Math.ceil(pagination.totalCount / pageSize)}
                        setCurrentPage={setPage}
                    />
                </PaginationWrapper>
            )}
        </Wrapper>
    );
};

export default PaginatedResources;
