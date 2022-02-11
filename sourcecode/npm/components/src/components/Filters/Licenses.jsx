import React from 'react';
import useFetchWithToken from '../../hooks/useFetchWithToken';
import useConfig from '../../hooks/useConfig';
import useTranslation from '../../hooks/useTranslation';
import {
    Checkbox,
    CircularProgress,
    List,
    ListItem,
    ListItemIcon,
    ListItemText,
    makeStyles,
} from '@material-ui/core';

const useStyles = makeStyles((theme) => ({
    nested: {
        paddingLeft: theme.spacing(1),
    },
    checkboxRoot: {
        height: 20,
        boxSizing: 'border-box',
    },
    listItemIcon: {
        minWidth: 30,
    },
}));

const Licenses = ({ licenses }) => {
    const { t } = useTranslation();
    const { edlib } = useConfig();
    const classes = useStyles();

    const { loading, response } = useFetchWithToken(
        edlib(`/resources/v1/filters/licenses`)
    );

    if (!response || loading) {
        return <CircularProgress />;
    }

    return (
        <>
            <List
                dense
                component="div"
                disablePadding
                className={classes.nested}
            >
                {response
                    .map((item) => {
                        const parts = item.id.split('-');
                        return {
                            title: parts
                                .map((part) => t(`licenses.${part}`))
                                .join(' - '),
                            value: item.id,
                        };
                    })
                    .sort((a, b) =>
                        a.title < b.title ? -1 : a.title > b.title ? 1 : 0
                    )
                    .map((license) => (
                        <ListItem
                            button
                            dense
                            onClick={() => licenses.toggle(license)}
                        >
                            <ListItemIcon
                                dense
                                classes={{
                                    root: classes.listItemIcon,
                                }}
                            >
                                <Checkbox
                                    size="small"
                                    edge="start"
                                    checked={licenses.has(license)}
                                    tabIndex={-1}
                                    disableRipple
                                    color="primary"
                                    classes={{
                                        root: classes.checkboxRoot,
                                    }}
                                />
                            </ListItemIcon>
                            <ListItemText primary={license.title} />
                        </ListItem>
                    ))}
            </List>
        </>
    );
};

export default Licenses;
