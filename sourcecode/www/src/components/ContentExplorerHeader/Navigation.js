import React from 'react';
import { makeStyles } from 'tss-react/mui';
import useTranslation from '../../hooks/useTranslation';
import { matchPath, useHistory, useLocation } from 'react-router-dom';
import Box from '@mui/material/Box';
import AddCircleRounded from '@mui/icons-material/AddCircleRounded';
import ShoppingCart from '@mui/icons-material/ShoppingCart';
import Home from '@mui/icons-material/Home';
import LinkNavigation from './LinkNavigation';
import MenuNavigation from './MenuNavigation';

const useStyles = makeStyles()((theme) => {
    return {
        links: {
            justifyContent: 'center',
            flex: 3,
            '& > *': {
                padding: theme.spacing(1),
            },
        },
    };
});

export default ({ activatedEditors, getUrl }) => {
    const { t } = useTranslation();
    const location = useLocation();
    const history = useHistory();
    const { classes } = useStyles();

    const isActive = (path) => {
        const paths = Array.isArray(path) ? [...path] : [path];

        return paths.some((path) => {
            const matchAgainst = path.includes('?') ? location.pathname + location.search : location.pathname;

            return matchPath(matchAgainst, {
                path,
            });
        });
    };

    const navigationItems = [
        {
            id: 'shared-content',
            title: t('Delt innhold'),
            active: isActive(getUrl('/shared-content')),
            icon: <ShoppingCart fontSize="small" />,
            action: () => history.push(getUrl('/shared-content')),
        },{
            id: 'my-content',
            title: t('Mitt innhold'),
            active: isActive(getUrl('/my-content')),
            icon: <Home fontSize="small" />,
            action: () => history.push(getUrl('/my-content')),
        }
    ];
    if (activatedEditors.length === 1) {
        navigationItems.unshift(
            {
                id: 'create-content',
                title: t('Opprett innhold'),
                active: isActive(activatedEditors[0][1].link),
                icon: <AddCircleRounded fontSize="small" />,
                action: () => history.push(activatedEditors[0][1].link),
            },
        );
    } else {
        navigationItems.unshift(
            {
                id: 'create-content',
                title: t('Opprett innhold'),
                active: isActive([
                    getUrl('/resources/new'),
                    getUrl('/link-author'),
                    getUrl('/doku-author'),
                ]),
                icon: <AddCircleRounded fontSize="small" />,
                items: activatedEditors.map(
                    ([type, { link, label }]) => ({
                        id: type,
                        action: () => history.push(link),
                        title: label,
                        active: isActive(link),
                    }),
                ),
            }
        );
    }

    return (
        <>
            <Box
                className={classes.links}
                sx={{display: { xs: 'none', md: 'flex' } }}
            >
                <LinkNavigation items={navigationItems} />
            </Box>
            <Box sx={{display: { xs: 'flex', md: 'none' } }}>
                <MenuNavigation items={navigationItems} />
            </Box>
        </>
    );
}
