import React from 'react';
import { Drawer } from '@mui/material';

export default ({
    onClose,
    isOpen = false,
    children,
}) => {

    return (
        <Drawer
            variant="temporary"
            anchor="right"
            open={isOpen}
            PaperProps={{
                sx: {
                    width: '85vw',
                }
            }}
            onClose={onClose}
        >
            {children}
        </Drawer>
    );
};
