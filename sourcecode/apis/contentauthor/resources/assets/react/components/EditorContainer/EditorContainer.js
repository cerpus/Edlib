import React from 'react';
import PropTypes from 'prop-types';
import Container from '@material-ui/core/Container';
import cn from 'clsx';
import Tabs from '@material-ui/core/Tabs';
import Tab from '@material-ui/core/Tab';
import Paper from '@material-ui/core/Paper';
import { makeStyles } from '@material-ui/core/styles';

const useStyle = makeStyles((theme) => ({
    tab: {
        backgroundColor: theme.palette.background.paper,
    },
    firstTab: {
        borderTopLeftRadius: theme.shape.borderRadius,
    },
    lastTab: {
        borderTopRightRadius: theme.shape.borderRadius,
    },
    singleTab: {
        borderTopLeftRadius: theme.shape.borderRadius,
        borderTopRightRadius: theme.shape.borderRadius,
    },
    noTabsPaper: {
        borderRadius: theme.shape.borderRadius,
    },
    tabsPaper: {
        borderTopLeftRadius: 0,
    },
    childContainer: {
        padding: 10,
    },
}));

const EditorContainer = ({
    tabs,
    activeTab,
    onTabChange,
    sidebar,
    className,
    containerClassname,
    children,
}) => {
    const classes = useStyle();

    const onChangeTab = async (e, selected) => {
        onTabChange(selected);
    };

    return (
        <Container
            maxWidth="lg"
            className={cn('editorContainer', containerClassname)}
        >
            <div className="editorMainPaper">
                {tabs.length > 0 &&
                    <Tabs
                        value={activeTab}
                        onChange={onChangeTab}
                    >
                        {tabs.map(({label, value}, index) =>
                            <Tab
                                key={value}
                                value={value}
                                label={label}
                                className={cn(
                                    classes.tab,
                                    {
                                        [classes.firstTab]: tabs.length > 1 && index === 0,
                                        [classes.lastTab]: tabs.length > 1 && index === tabs.length - 1,
                                        [classes.singleTab]: tabs.length === 1,
                                    }
                                )}
                            />
                        )}
                    </Tabs>
                }
                <Paper
                    elevation={2}
                    className={cn(
                        'editorPaper',
                        className,
                        classes.noTabsPaper,
                        {
                            [classes.tabsPaper]: tabs.length,
                        }
                    )}
                >
                    <div
                        className={cn(
                            "editorMainContainer",
                            classes.childContainer,
                        )}
                    >
                        {children}
                    </div>
                </Paper>
            </div>
            {sidebar}
        </Container>
    );
};

EditorContainer.propTypes = {
    tabs: PropTypes.array,
    activeTab: PropTypes.oneOfType([PropTypes.number, PropTypes.string]),
    onTabChange: PropTypes.func,
    sidebar: PropTypes.oneOfType([PropTypes.element, PropTypes.bool]),
    className: PropTypes.string,
    containerClassname: PropTypes.string,
    children: PropTypes.oneOfType([PropTypes.object, PropTypes.array]),
};

EditorContainer.defaultProps = {
    tabs: [],
    activeTab: null,
    onTabChange: () => {},
    sidebar: null,
    className: '',
    containerClassname: '',
};

export default EditorContainer;
