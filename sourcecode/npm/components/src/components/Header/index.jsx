import React from 'react';
import {
    AddCircleRounded,
    Home,
    ShoppingCart,
    Close,
} from '@material-ui/icons';
import {
    AppBar,
    Button,
    makeStyles,
    Menu,
    MenuItem,
    Toolbar,
} from '@material-ui/core';
import styled from 'styled-components';
import cn from 'classnames';
import { useLocation, matchPath, useHistory } from 'react-router-dom';
import { useConfigurationContext } from '../../contexts/Configuration';
import useTranslation from '../../hooks/useTranslation';
import { useEdlibComponentsContext } from '../../contexts/EdlibComponents';
import resourceEditors from '../../constants/resourceEditors';
import logoUrl from '../../assets/edlib.png';

const StyledClose = styled.div`
    display: flex;
    flex-direction: row-reverse;
    align-items: center;
    padding-right: 15px;
    color: white;

    & > svg {
        width: 40px;
        height: 40px;
        cursor: pointer;
    }
`;

const useStyles = makeStyles((theme) => {
    return {
        logo: {
            maxHeight: 60,
        },
        toolbar: {
            justifyContent: 'space-between',
        },
        links: {
            'display': 'flex',
            'justifyContent': 'center',
            'flex': 3,
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
    };
});

const CustomHeader = ({ onClose }) => {
    const { t } = useTranslation();
    const classes = useStyles();
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
            link: '/resources/new/contentauthor?group=h5p',
            label: t('Interaktivitet'),
        },
        [resourceEditors.QUESTION_SET]: {
            link: '/resources/new/contentauthor?group=questionset',
            label: t('Spørsmål'),
        },
        [resourceEditors.ARTICLE]: {
            link: '/resources/new/contentauthor?group=article',
            label: t('Tekst'),
        },
        // [resourceEditors.EMBED]: {
        //     link: '/resources/new/url',
        //     label: 'Link',
        // },
        [resourceEditors.DOKU]: {
            link: '/resources/new/doku',
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
                                    className={buttonClasses(
                                        isActive([
                                            '/resources/new',
                                            '/link-author',
                                            '/doku-author',
                                        ])
                                    )}
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
                                    className={buttonClasses(
                                        isActive(
                                            activatedEditorsList[0][1].link
                                        )
                                    )}
                                >
                                    {t('Opprett innhold')}
                                </Button>
                            </div>
                        )}
                        <div>
                            <Button
                                onClick={() => {
                                    history.push('/shared-content');
                                    handleClose();
                                }}
                                color="inherit"
                                startIcon={<ShoppingCart />}
                                className={buttonClasses(
                                    isActive('/shared-content')
                                )}
                            >
                                {t('Delt innhold')}
                            </Button>
                        </div>
                        <div>
                            <Button
                                onClick={() => {
                                    history.push('/my-content');
                                    handleClose();
                                }}
                                color="inherit"
                                startIcon={<Home />}
                                className={buttonClasses(
                                    isActive('/my-content')
                                )}
                            >
                                {t('Mitt innhold')}
                            </Button>
                        </div>
                    </div>
                )}
                {onClose ? (
                    <StyledClose>
                        <Close onClick={onClose} />
                    </StyledClose>
                ) : (
                    <div>&nbsp</div>
                )}
            </Toolbar>
        </AppBar>
    );
};

export default CustomHeader;
