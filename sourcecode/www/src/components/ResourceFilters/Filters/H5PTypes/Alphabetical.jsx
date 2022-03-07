import React from 'react';
import {
    Checkbox,
    List,
    ListItem,
    ListItemIcon,
    ListItemText,
} from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

const useStyles = makeStyles((theme) => ({
    checkboxRoot: {
        height: 20,
        boxSizing: 'border-box',
    },
    listItemIcon: {
        minWidth: 30,
    },
}));

const Alphabetical = ({ allH5ps, contentTypes }) => {
    const classes = useStyles();

    return (
        <List dense component="div" disablePadding>
            {allH5ps.map((h5p, index) => (
                <ListItem
                    key={index}
                    button
                    dense
                    onClick={() => contentTypes.toggle(h5p)}
                >
                    <ListItemIcon
                        classes={{
                            root: classes.listItemIcon,
                        }}
                    >
                        <Checkbox
                            size="small"
                            edge="start"
                            checked={contentTypes.has(h5p)}
                            tabIndex={-1}
                            disableRipple
                            color="primary"
                            classes={{
                                root: classes.checkboxRoot,
                            }}
                        />
                    </ListItemIcon>
                    <ListItemText
                        primary={`${h5p.title} (${h5p.filteredCount})`}
                    />
                </ListItem>
            ))}
        </List>
    );
};

export default Alphabetical;
