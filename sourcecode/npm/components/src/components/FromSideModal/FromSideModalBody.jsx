import React from 'react';
import { Box } from '@material-ui/core';

export default ({ children }) => {
    return (
        <Box px={5} py={2}>
            {children}
        </Box>
    );
};
