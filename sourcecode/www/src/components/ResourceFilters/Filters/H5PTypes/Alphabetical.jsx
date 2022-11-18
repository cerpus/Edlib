import React from 'react';
import {
    Checkbox,
    List,
    ListItemButton,
    ListItemIcon,
    ListItemText,
} from '@mui/material';
import { makeStyles } from 'tss-react/mui';

const useStyles = makeStyles()((theme) => ({
    checkboxRoot: {
        height: 20,
        boxSizing: 'border-box',
    },
    listItemIcon: {
        minWidth: 30,
    },
}));

const Alphabetical = ({ allH5ps, contentTypes }) => {
    const { classes } = useStyles();

    return (
        <List dense component="div" disablePadding>
            {allH5ps.map((h5p, index) => (
                <ListItemButton
                    key={index}
                    dense
                    onClick={() => contentTypes.toggle(h5p)}
                    disabled = {h5p.filteredCount === 0}
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
                </ListItemButton>
            ))}
        </List>
    );
};

export default Alphabetical;
