import React from 'react';
import AddCircleRounded from '@mui/icons-material/AddCircleRounded';
import ArrowRight from '@mui/icons-material/ArrowRight';
import MenuIcon from '@mui/icons-material/Menu';
import IconButton from '@mui/material/IconButton';
import ListItem from '@mui/material/ListItem';
import ListItemIcon from '@mui/material/ListItemIcon';
import Menu from '@mui/material/Menu';
import MenuItem from '@mui/material/MenuItem';

export default ({ items }) => {
    const [anchorElNav, setAnchorElNav] = React.useState(null);
    const handleOpenNavMenu = (event) => {
        setAnchorElNav(event.currentTarget);
    };
    const handleCloseNavMenu = () => {
        setAnchorElNav(null);
    };

    return (
        <>
            <IconButton
                size="large"
                aria-controls="menu-appbar"
                aria-haspopup="true"
                onClick={handleOpenNavMenu}
                color="inherit"
            >
                <MenuIcon />
            </IconButton>
            <Menu
                id="menu-appbar"
                anchorEl={anchorElNav}
                anchorOrigin={{
                    vertical: 'bottom',
                    horizontal: 'left',
                }}
                keepMounted
                transformOrigin={{
                    vertical: 'top',
                    horizontal: 'left',
                }}
                open={Boolean(anchorElNav)}
                onClose={handleCloseNavMenu}
                sx={{
                    display: { xs: 'block', md: 'none' },
                }}
            >
                {items.map(({ id, items, icon, active, title, action }) => {
                    if (items) {
                        return [
                            <ListItem
                                key={id}
                            >
                                <ListItemIcon
                                    sx={{
                                        minWidth: 36,
                                    }}
                                >
                                    <AddCircleRounded fontSize="small" />
                                </ListItemIcon>
                                {title}
                            </ListItem>,
                            items.map(
                                ({ id, title, action, active }) => (
                                    <MenuItem
                                        key={id}
                                        onClick={() => {
                                            action()
                                            handleCloseNavMenu();
                                        }}
                                        sx={{
                                            pl: 4,
                                            backgroundColor: active ? 'secondary.main' : 'default',
                                        }}
                                    >
                                        <ListItemIcon>
                                            <ArrowRight fontSize="small"/>
                                        </ListItemIcon>
                                        {title}
                                    </MenuItem>
                                )
                            )
                        ];
                    } else {
                        return (
                            <MenuItem
                                key={id}
                                onClick={() => {
                                    action()
                                    handleCloseNavMenu();
                                }}
                                sx={{
                                    backgroundColor: active ? 'secondary.main' : 'default',
                                }}
                            >
                                <ListItemIcon>{icon}</ListItemIcon>
                                {title}
                            </MenuItem>
                        );
                    }
                })}
            </Menu>
        </>
    );
}
