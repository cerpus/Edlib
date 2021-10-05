import React from 'react';
import PropTypes from 'prop-types';
import { Table, TableBody, TableCell, TableRow, Tooltip } from '@material-ui/core';
import { withStyles } from '@material-ui/core/styles';

function ContentPropertiesLayout(props) {
    const {
        classes,
        rows,
    } = props;
    return (
        <Table className={classes.table}>
            <TableBody>
                {rows.map(row => {
                    let contentPropertiesRowClassName = 'contentpropertiesrow';
                    if (typeof row.cssClassName !== 'undefined') {
                        contentPropertiesRowClassName += ' ' + row.cssClassName;
                    }
                    let displayValue = row.value;
                    if (typeof row.fullValue !== 'undefined') {
                        displayValue = (
                            <Tooltip
                                title={row.fullValue}
                                classes={{ tooltip: classes.tooltip }}
                                placement="top"
                            >
                                <span>{row.value}</span>
                            </Tooltip>
                        );
                    }
                    return (
                        <TableRow className={contentPropertiesRowClassName} key={row.label}>
                            <TableCell className="contentpropertieslabel">{row.label}</TableCell>
                            <TableCell className="contentpropertiesvalue">
                                {displayValue}
                            </TableCell>
                        </TableRow>
                    );
                })}
            </TableBody>
        </Table>
    );
}

ContentPropertiesLayout.propTypes = {
    classes: PropTypes.object.isRequired,
    rows: PropTypes.array,
};

const styles = theme => ({
    root: {
        width: '100%',
        marginTop: theme.spacing(3),
        overflowX: 'auto',
    },
    table: {
        '& td': {
            fontSize: 'inherit',
            padding: '4px',
        },
        '& tr': {
            height: 'auto',
        },
    },
    tooltip: {
        fontSize: 'inherit',
    },
});

export default withStyles(styles)(ContentPropertiesLayout);
