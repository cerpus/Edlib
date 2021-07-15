import React from 'react';
import { makeStyles } from '@material-ui/core/styles';
import {
    Menu,
    MenuItem,
    Typography,
    Toolbar,
    AppBar,
    Button,
} from '@material-ui/core';
import authContext from '../../contexts/auth.js';
import configContext from '../../contexts/config.js';
import { Link } from 'react-router-dom';

const useStyles = makeStyles((theme) => ({
    grow: {
        flexGrow: 1,
    },
    menuButton: {
        marginRight: theme.spacing(2),
    },
    sectionDesktop: {
        display: 'none',
        [theme.breakpoints.up('md')]: {
            display: 'flex',
        },
    },
}));

export default ({ className }) => {
    const classes = useStyles();
    const [anchorEl, setAnchorEl] = React.useState(null);
    const { isAuthenticated, user, loginUrl } = React.useContext(authContext);
    const { authUrl, logoutRedirectUrl } = React.useContext(configContext);

    const logoutUrl = `${authUrl}/logout?returnUrl=${logoutRedirectUrl}`;

    const isMenuOpen = Boolean(anchorEl);

    const handleProfileMenuOpen = (event) => {
        setAnchorEl(event.currentTarget);
    };

    const handleMenuClose = () => {
        setAnchorEl(null);
    };

    const menuId = 'primary-search-account-menu';

    return (
        <>
            <AppBar position="fixed" className={className}>
                <Toolbar>
                    <Button
                        component={Link}
                        to="/"
                        onClick={() => console.log('here')}
                        color="inherit"
                    >
                        <Typography variant="h6" noWrap>
                            Edlib admin
                        </Typography>
                    </Button>
                    <div className={classes.grow} />
                    <div className={classes.sectionDesktop}>
                        {!isAuthenticated && (
                            <Button
                                color="inherit"
                                component="a"
                                href={loginUrl}
                            >
                                Login
                            </Button>
                        )}
                        {user && (
                            <>
                                <Button
                                    component={Link}
                                    to="/system-status"
                                    color="inherit"
                                >
                                    System status
                                </Button>
                                <Button
                                    onClick={handleProfileMenuOpen}
                                    aria-controls={menuId}
                                    color="inherit"
                                >
                                    {user.firstName} {user.lastName}
                                </Button>
                            </>
                        )}
                    </div>
                </Toolbar>
            </AppBar>
            <Menu
                anchorEl={anchorEl}
                anchorOrigin={{ vertical: 'top', horizontal: 'right' }}
                id={menuId}
                keepMounted
                transformOrigin={{ vertical: 'top', horizontal: 'right' }}
                open={isMenuOpen}
                onClose={handleMenuClose}
            >
                <MenuItem component="a" href={logoutUrl}>
                    Logg ut
                </MenuItem>
            </Menu>
        </>
    );
};
