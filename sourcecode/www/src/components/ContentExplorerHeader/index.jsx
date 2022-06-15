import React from 'react';
import {
    AddCircleRounded,
    Home,
    ShoppingCart,
    Close,
} from '@mui/icons-material';
import { AppBar, Button, Menu, MenuItem, Toolbar } from '@mui/material';
import { makeStyles } from 'tss-react/mui';
import cn from 'classnames';
import { useLocation, matchPath, useHistory } from 'react-router-dom';
import { useConfigurationContext } from '../../contexts/Configuration';
import useTranslation from '../../hooks/useTranslation';
import { useEdlibComponentsContext } from '../../contexts/EdlibComponents';
import resourceEditors from '../../constants/resourceEditors';
import logoUrl from '../../assets/edlib.png';

const useStyles = makeStyles()((theme) => {
    return {
        logo: {
            maxHeight: 60,
        },
        toolbar: {
            justifyContent: 'space-between',
        },
        links: {
            display: 'flex',
            justifyContent: 'center',
            flex: 3,
            '& > *': {
                padding: theme.spacing(1),
            },
        },
        selectedButton: {
            color: theme.palette.secondary.main,
        },
        headerButton: {
            '&:hover': {
                color: theme.palette.secondary.main,
            },
        },
        close: {
            display: 'flex',
            flexDirection: 'row-reverse',
            alignItems: 'center',
            paddingRight: 15,
            color: 'white',
            '& > svg': {
                width: 40,
                height: 40,
                cursor: 'pointer',
            },
        },
    };
});

const ContentExplorerHeader = ({ onClose, getUrl }) => {
    const { t } = useTranslation();
    const { classes } = useStyles();
    const location = useLocation();
    const history = useHistory();
    const { enableDoku, inMaintenanceMode } = useConfigurationContext();
    const { getUserConfig } = useEdlibComponentsContext();

    const isActive = (path) => {
        let paths = [path];

        if (Array.isArray(path)) {
            paths = [...path];
        }

        return paths.some((path) =>
            matchPath(location.pathname, {
                path,
                exact: false,
            })
        );
    };

    const enabledTypes =
        getUserConfig('enabledResourceTypes') || Object.values(resourceEditors);

    const isEditorEnabled = (type) => enabledTypes.indexOf(type) !== -1;

    const editorMapping = {
        [resourceEditors.H5P]: {
            link: getUrl('/resources/new/contentauthor?group=h5p'),
            label: t('Interaktivitet'),
        },
        [resourceEditors.QUESTION_SET]: {
            link: getUrl('/resources/new/contentauthor?group=questionset'),
            label: t('Spørsmål'),
        },
        // [resourceEditors.ARTICLE]: {
        //     link: getUrl('/resources/new/contentauthor?group=article'),
        //     label: t('Tekst'),
        // },
        // [resourceEditors.EMBED]: {
        //     link: '/resources/new/url',
        //     label: 'Link',
        // },
        [resourceEditors.DOKU]: {
            link: getUrl('/resources/new/doku'),
            label: 'EdStep',
        },
    };

    const activatedEditorsList = Object.entries(editorMapping)
        .filter(([type]) => isEditorEnabled(type))
        .filter(([type]) => {
            switch (type) {
                case resourceEditors.DOKU:
                    return enableDoku;
                default:
                    return true;
            }
        });
    //******************************************************************************
    //************ New Component ***************************************************
    //******************************************************************************
    const [anchorEl, setAnchorEl] = React.useState(null);
    const open = Boolean(anchorEl);

    const handleMenu = (event) => {
        setAnchorEl(event.currentTarget);
    };

    const handleClose = () => {
        setAnchorEl(null);
    };

    const buttonClasses = (active) =>
        cn(classes.headerButton, {
            [classes.selectedButton]: active,
        });

    return (
        <AppBar position="static" className={classes.appBar}>
            <Toolbar className={classes.toolbar}>
                <div>
                    <img
                        className={classes.logo}
                        src={logoUrl}
                        alt="edlib logo"
                    />
                </div>
                {!inMaintenanceMode && (
                    <div className={classes.links}>
                        {activatedEditorsList.length > 1 && (
                            <div>
                                <Button
                                    aria-controls="menu-appbar"
                                    aria-haspopup="true"
                                    onClick={handleMenu}
                                    color="inherit"
                                    startIcon={<AddCircleRounded />}
                                    sx={{
                                        color: isActive([
                                            getUrl('/resources/new'),
                                            getUrl('/link-author'),
                                            getUrl('/doku-author'),
                                        ])
                                            ? 'secondary.main'
                                            : 'default',
                                    }}
                                    className={classes.headerButton}
                                >
                                    {t('Opprett innhold')}
                                </Button>
                                <Menu
                                    id="menu-appbar"
                                    anchorEl={anchorEl}
                                    keepMounted
                                    getContentAnchorEl={null}
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
                                    {activatedEditorsList.map(
                                        ([type, { link, label }]) => (
                                            <MenuItem
                                                onClick={() => {
                                                    history.push(link);
                                                    handleClose();
                                                }}
                                                key={type}
                                            >
                                                {label}
                                            </MenuItem>
                                        )
                                    )}
                                </Menu>
                            </div>
                        )}
                        {activatedEditorsList.length === 1 && (
                            <div>
                                <Button
                                    onClick={() => {
                                        history.push(
                                            activatedEditorsList[0][1].link
                                        );
                                        handleClose();
                                    }}
                                    color="inherit"
                                    startIcon={<AddCircleRounded />}
                                    sx={{
                                        color: isActive(
                                            activatedEditorsList[0][1].link
                                        )
                                            ? 'secondary.main'
                                            : 'default',
                                    }}
                                    className={classes.headerButton}
                                >
                                    {t('Opprett innhold')}
                                </Button>
                            </div>
                        )}
                        <div>
                            <Button
                                onClick={() => {
                                    history.push(getUrl('/shared-content'));
                                    handleClose();
                                }}
                                color="inherit"
                                startIcon={<ShoppingCart />}
                                sx={{
                                    color: isActive(getUrl('/shared-content'))
                                        ? 'secondary.main'
                                        : 'default',
                                }}
                                className={classes.headerButton}
                            >
                                {t('Delt innhold')}
                            </Button>
                        </div>
                        <div>
                            <Button
                                onClick={() => {
                                    history.push(getUrl('/my-content'));
                                    handleClose();
                                }}
                                color="inherit"
                                startIcon={<Home />}
                                sx={{
                                    color: isActive(getUrl('/my-content'))
                                        ? 'secondary.main'
                                        : 'default',
                                }}
                                className={classes.headerButton}
                            >
                                {t('Mitt innhold')}
                            </Button>
                        </div>
                    </div>
                )}
                {onClose ? (
                    <div className={classes.close}>
                        <Close onClick={onClose} />
                    </div>
                ) : (
                    <div>&nbsp</div>
                )}
            </Toolbar>
        </AppBar>
    );
};

export default ContentExplorerHeader;
