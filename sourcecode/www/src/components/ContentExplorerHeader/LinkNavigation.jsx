import React, { Fragment } from 'react';
import Button from '@mui/material/Button';
import { makeStyles } from 'tss-react/mui';
import Menu from '@mui/material/Menu';
import MenuItem from '@mui/material/MenuItem';

const useStyles = makeStyles()((theme) => {
    return {
        headerButton: {
            '&:hover': {
                color: theme.palette.secondary.main,
            },
        },
    };
});

export default ({ items }) => {
    const { classes } = useStyles();
    const [anchorEl, setAnchorEl] = React.useState(null);
    const open = Boolean(anchorEl);

    const handleMenu = (event) => {
        setAnchorEl(event.currentTarget);
    };

    const handleClose = () => {
        setAnchorEl(null);
    };

    return items.map(({ id, items, icon, active, title, action }) => {
        if (items) {
            return (
                <Fragment key={id}>
                    <Button
                        aria-controls="menu-appbar"
                        aria-haspopup="true"
                        onClick={handleMenu}
                        color="inherit"
                        startIcon={icon}
                        sx={{
                            color: active ? 'secondary.main' : 'default',
                        }}
                        className={classes.headerButton}
                    >
                        {title}
                    </Button>
                    <Menu
                        id="menu-appbar"
                        anchorEl={anchorEl}
                        keepMounted
                        anchorOrigin={{
                            vertical: 'bottom',
                            horizontal: 'center',
                        }}
                        transformOrigin={{
                            vertical: 'top',
                            horizontal: 'center',
                        }}
                        open={open}
                        onClose={handleClose}
                    >
                        {items.map(({ id, action, active, title}) => (
                            <MenuItem
                                key={id}
                                onClick={() => {
                                    action();
                                    handleClose();
                                }}
                                sx={{
                                    backgroundColor: active ? 'secondary.main' : 'default',
                                }}
                            >
                                {title}
                            </MenuItem>
                        ))}
                    </Menu>
                </Fragment>
            );
        } else {
            return (
                <Button
                    key={id}
                    onClick={action}
                    color="inherit"
                    startIcon={icon}
                    className={classes.headerButton}
                    sx={{
                        color: active ? 'secondary.main' : 'default',
                    }}
                >
                    {title}
                </Button>
            );
        }
    });
}
