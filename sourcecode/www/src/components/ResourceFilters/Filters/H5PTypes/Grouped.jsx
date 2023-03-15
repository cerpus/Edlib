import React from 'react';
import {
    Checkbox,
    Collapse,
    List,
    ListItemButton,
    ListItemIcon,
    ListItemText,
} from '@mui/material';
import { ExpandLess, ExpandMore } from '@mui/icons-material';
import { makeStyles } from 'tss-react/mui';
import _ from 'lodash';
import useTranslation from '../../../../hooks/useTranslation.js';
import contentAuthorConstants from '../../../../constants/contentAuthor.js';
import useArray from '../../../../hooks/useArray.js';

const useStyles = makeStyles()((theme) => ({
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
    listItemText: {
        fontSize: '1rem !important',
        fontWeight: '400',
    },
}));

const Grouped = ({ allH5ps, contentTypes }) => {
    const { t } = useTranslation();
    const { classes } = useStyles();
    const open = useArray();

    const categoriesObject = allH5ps.reduce((categories, h5p) => {
        let groups = contentAuthorConstants.groups.filter(
            (group) => group.contentTypes.indexOf(h5p.value) !== -1
        );
        if (groups.length === 0) {
            groups.push({
                translationKey: 'others',
                order: null,
            });
        }

        groups.forEach((group) => {
            if (!categories[group.translationKey]) {
                categories[group.translationKey] = {
                    translationKey: group.translationKey,
                    order: group.order,
                    contentTypes: [],
                };
            }

            categories[group.translationKey].contentTypes.push(h5p);
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
            {categories.map((category, index) => (
                <React.Fragment key={index}>
                    <ListItemButton
                        onClick={() => open.toggle(category.translationKey)}
                    >
                        <ListItemText
                            classes={{
                                root: classes.listItemText,
                            }}
                        >
                            {_.capitalize(
                                t(
                                    `content_type_groups.${category.translationKey}`
                                )
                            )}
                        </ListItemText>
                        {open.has(category.translationKey) ? (
                            <ExpandLess />
                        ) : (
                            <ExpandMore />
                        )}
                    </ListItemButton>
                    <Collapse
                        in={open.has(category.translationKey)}
                        timeout="auto"
                        unmountOnExit
                    >
                        <List dense component="div" disablePadding>
                            {category.contentTypes.map((h5p, index) => (
                                <ListItemButton
                                    key={index}
                                    dense
                                    onClick={() => contentTypes.toggle(h5p)}
                                    disabled={h5p.filteredCount === 0}
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
                                        classes={{
                                            root: classes.listItemText,
                                        }}
                                    />
                                </ListItemButton>
                            ))}
                        </List>
                    </Collapse>
                </React.Fragment>
            ))}
        </List>
    );
};

export default Grouped;
