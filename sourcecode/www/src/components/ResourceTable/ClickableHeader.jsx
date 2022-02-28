import React from 'react';
import { TableSortLabel } from '@mui/material';

const ClickableHeader = ({
    style,
    sortingOrder,
    setSortingOrder,
    children,
    name,
}) => {
    const ascName = name + '(asc)';
    const descName = name + '(desc)';
    const active = [ascName, descName].indexOf(sortingOrder) !== -1;

    return (
        <div>
            <div>
                <TableSortLabel
                    style={style}
                    active={active}
                    direction={sortingOrder === ascName ? 'asc' : 'desc'}
                    onClick={() =>
                        setSortingOrder(
                            sortingOrder === descName ? ascName : descName
                        )
                    }
                >
                    {children}
                </TableSortLabel>
            </div>
        </div>
    );
};

export default ClickableHeader;
