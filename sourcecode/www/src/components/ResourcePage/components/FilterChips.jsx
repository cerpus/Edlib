import React from 'react';
import { Box, Chip, makeStyles } from '@material-ui/core';

const useStyles = makeStyles((theme) => ({
    chip: {
        margin: theme.spacing(0.5),
    },
}));

const FilterChips = ({ chips, color = 'secondary' }) => {
    const classes = useStyles();

    return (
        <Box paddingY={1}>
            {chips.map((chip) => (
                <Chip
                    key={chip.value}
                    label={chip.title}
                    onDelete={chip.onDelete}
                    color={color}
                    className={classes.chip}
                />
            ))}
        </Box>
    );
    // return (
    //     <Box paddingY={1}>
    //         {filters.contentTypes.value.map((contentType, index) => (
    //             <Chip
    //                 key={contentType.value}
    //                 label={contentType.title}
    //                 onDelete={
    //                     allowChange
    //                         ? () => filters.contentTypes.removeIndex(index)
    //                         : null
    //                 }
    //                 color={color}
    //                 className={classes.chip}
    //             />
    //         ))}
    //         {filters.licenses.value.map((license, index) => (
    //             <Chip
    //                 key={license.value}
    //                 label={license.title}
    //                 onDelete={
    //                     allowChange
    //                         ? () => filters.licenses.removeIndex(index)
    //                         : null
    //                 }
    //                 color={color}
    //                 className={classes.chip}
    //             />
    //         ))}
    //     </Box>
    // );
};

export default FilterChips;
