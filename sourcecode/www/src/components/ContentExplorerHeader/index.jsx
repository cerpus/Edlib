import React from 'react';
import Close from '@mui/icons-material/Close';
import AppBar from '@mui/material/AppBar';
import Toolbar from '@mui/material/Toolbar';
import { makeStyles } from 'tss-react/mui';
import { useConfigurationContext } from '../../contexts/Configuration';
import useTranslation from '../../hooks/useTranslation';
import { useEdlibComponentsContext } from '../../contexts/EdlibComponents';
import resourceEditors from '../../constants/resourceEditors';
import logoUrl from '../../assets/edlib.png';
import Navigation from './Navigation';

const useStyles = makeStyles()((theme) => {
    return {
        logo: {
            maxHeight: 60,
        },
        toolbar: {
            justifyContent: 'space-between',
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
                '&:hover': {
                    '@media(forced-colors: active)': {
                        stroke: 'Highlight',
                        outline: '1px solid Highlight',
                    },
                },
            },
        },
    };
});

const ContentExplorerHeader = ({ onClose, getUrl }) => {
    const { t } = useTranslation();
    const { classes } = useStyles();
    const { inMaintenanceMode } = useConfigurationContext();
    const { getUserConfig } = useEdlibComponentsContext();

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
    };

    const activatedEditorsList = Object.entries(editorMapping)
        .filter(([type]) => isEditorEnabled(type));

    return (
        <AppBar position="static">
            <Toolbar className={classes.toolbar}>
                <div>
                    <img
                        className={classes.logo}
                        src={logoUrl}
                        alt="edlib logo"
                    />
                </div>
                {!inMaintenanceMode && (
                    <Navigation
                        activatedEditors={activatedEditorsList}
                        getUrl={getUrl}
                    />
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
