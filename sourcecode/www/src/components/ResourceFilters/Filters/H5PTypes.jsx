import React from 'react';
import {
    Checkbox,
    CircularProgress,
    Collapse,
    List,
    ListItem,
    ListItemIcon,
    ListItemText,
    makeStyles,
} from '@material-ui/core';
import { ExpandLess, ExpandMore } from '@material-ui/icons';

import useTranslation from '../../../hooks/useTranslation.js';
import useArray from '../../../hooks/useArray.js';
import contentAuthorConstants from '../../../constants/contentAuthor.js';

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

const H5PTypes = ({ contentTypes, filterCount, contentTypeData }) => {
    const { t } = useTranslation();
    const classes = useStyles();

    const open = useArray();

    const allH5ps = contentTypeData
        .map((item) => {
            const count = filterCount.find(
                (filterCount) => filterCount.key === item.contentType
            );
            return {
                title: item.title,
                value: item.contentType,
                filteredCount: count ? count.count : 0,
            };
        })
        .sort((a, b) => (a.title < b.title ? -1 : a.title > b.title ? 1 : 0));

    const categoriesObject = allH5ps.reduce((categories, h5p) => {
        let groups = contentAuthorConstants.groups.filter(
            (group) => group.contentTypes.indexOf(h5p.value) !== -1
        );
        if (groups.length === 0) {
            groups.push({
                name: 'Others',
                order: null,
            });
        }
        groups.forEach((group) => {
            if (!categories[group.name]) {
                categories[group.name] = {
                    name: group.name,
                    order: group.order,
                    contentTypes: [],
                };
            }

            categories[group.name].contentTypes.push(h5p);
        });

        return categories;
    }, {});

    const categories = Object.values(categoriesObject).sort((a, b) => {
        if (a.order === null) {
            return 1;
        }
        if (b.order === null) {
            return -1;
        }

        return a.order - b.order;
    });

    return (
        <List dense component="div" disablePadding className={classes.nested}>
            {categories.map((category) => (
                <React.Fragment key={category.name}>
                    <ListItem button onClick={() => open.toggle(category.name)}>
                        <ListItemText>
                            <strong>{t(category.name)}</strong>
                        </ListItemText>
                        {open.has(category.name) ? (
                            <ExpandLess />
                        ) : (
                            <ExpandMore />
                        )}
                    </ListItem>
                    <Collapse
                        in={open.has(category.name)}
                        timeout="auto"
                        unmountOnExit
                    >
                        <List dense component="div" disablePadding>
                            {category.contentTypes.map((h5p) => (
                                <ListItem
                                    key={h5p.value}
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
                    </Collapse>
                </React.Fragment>
            ))}
        </List>
    );
};

export default H5PTypes;
