import React from 'react';
import {
    Pagination as PaginationComponent,
    PaginationButton,
} from '@cerpus/ui';

const Pagination = ({ currentPage, pageCount, setCurrentPage }) => {
    const leftDots = currentPage > 3;
    const rightDots = pageCount - currentPage > 3;

    const button = (page, text) => (
        <PaginationButton
            selected={currentPage === page}
            onClick={() => page != null && setCurrentPage(page)}
        >
            {text || page + 1}
        </PaginationButton>
    );

    return (
        <PaginationComponent
            onBack={() => currentPage > 0 && setCurrentPage(currentPage - 1)}
            onNext={() =>
                currentPage < pageCount - 1 && setCurrentPage(currentPage + 1)
            }
        >
            {leftDots && (
                <>
                    {button(0)}
                    {button(null, '...')}
                </>
            )}
            {[...Array(pageCount)]
                .map((x, i) => i)
                .filter((x) => {
                    if (x < currentPage) {
                        if (!leftDots) return true;

                        return x > currentPage - 2;
                    }

                    if (x > currentPage) {
                        if (!rightDots) return true;

                        return x < currentPage + 2;
                    }

                    return true;
                })
                .map((x) => (
                    <PaginationButton
                        key={x}
                        selected={currentPage === x}
                        onClick={() => setCurrentPage(x)}
                    >
                        {x + 1}
                    </PaginationButton>
                ))}
            {rightDots && (
                <>
                    {button(null, '...')}
                    {button(pageCount - 1)}
                </>
            )}
        </PaginationComponent>
    );
};

export default Pagination;
